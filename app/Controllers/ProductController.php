<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\API\ResponseTrait;
use App\Models\ProductModel;

class ProductController extends BaseController

{
    use ResponseTrait;
    public function getProducts()
    {   

        // Add CORS headers manually
        header('Access-Control-Allow-Origin: *'); // Or specify your frontend origin: 'http://localhost:5173'
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS'); // Or just GET if that's all you need
        header('Access-Control-Allow-Headers: Content-Type, Authorization'); // Add any headers your frontend sends

        $model = new ProductModel();
        $products = $model->findAll();

        // $data = [
        //     'products' => $products,
        // ];

        return $this->respond($products);
    }

    public function addProduct() {

        $model = new ProductModel();

        $name = $this->request->getPost('name');
        $price = $this->request->getPost('price');
        $file = $this->request->getFile('image');

        if(!empty($file)) {
            
            $fileName = $file->getRandomName();
            $filePath = FCPATH.'files\images\\';
            $fileUrl = base_url().'files/images/'.$fileName;

            $data = [
                'name_product' => $name,
                'price_product' => $price,
                'image_product' => $fileUrl,
                'filename_product' => $fileName,
                'filepath_product' => $filePath
            ];

            if($model->save($data)) {
                $file->move($filePath, $fileName);
                return redirect()->to(env('app_clientBaseURL'));
            } else {
                return $this->respond(['status'=>401, 'message'=>'File tidak ditemukan']);
            }
        }

    }
    
    public function updateProduct(){
        $model = new ProductModel();

        $productId = $this->request->getPost('id');

        // 2. Retrieve the existing product from the database
        $existingProduct = $model->where('id_product', $productId)->get()->getResult();

        // Check if the product exists
        if (!$existingProduct) {
            return $this->respond([
                'status' => 404,
                'message' => 'Product not found.'
            ], 404); // Return 404 Not Found if product doesn't exist
        }

        // 3. Get updated name and price from the request
        $name = $this->request->getPost('name');
        $price = $this->request->getPost('price');
        $stock = $this->request->getPost('stock');

        // Prepare data for update. Start with existing data to avoid overwriting with nulls.
        $dataToUpdate = [
            'name_product' => $name ?? $existingProduct[0]->name_product, // Use new name if provided, else keep old
            'price_product' => $price ?? $existingProduct[0]->price_product, // Use new price if provided, else keep old
            'stock_product' => $stock ?? $existingProduct[0]->stock_product
            // 'image_product' will be handled below
        ];

        // 4. Handle image update (if a new image is uploaded)
        $newFile = $this->request->getFile('image'); // Get the new file if uploaded

        // Check if a new file was actually uploaded and is valid
        if ($newFile && $newFile->isValid() && !$newFile->hasMoved()) {
            // Get the old image path to delete it later
            $oldImageFileName = basename($existingProduct[0]->image_product); // Assuming 'image_product' stores the URL

            // Generate a unique filename for the new image
            $newFileName = $newFile->getRandomName(); // Or use a more descriptive naming convention
            $newFilePath = FCPATH . 'files\\images\\';
            $newFileUrl = base_url() . 'files/images/' . $newFileName;

            // Move the new file to its destination
            try {
                $newFile->move(FCPATH . 'files/images/', $newFileName);

                // Update the image URL in the data to be saved
                $dataToUpdate['image_product'] = $newFileUrl;
                $dataToUpdate['filename_product'] = $newFileName;
                $dataToUpdate['filepath_product'] = $newFilePath;
                // 5. Delete the old image file if it exists and is different from the new one
                $oldImagePath = FCPATH . 'files/images/' . $oldImageFileName;
                if (!empty($oldImageFileName) && file_exists($oldImagePath) && $oldImageFileName !== $newFileName) {
                    unlink($oldImagePath); // Delete the old file from the server
                }

            } catch (\Exception $e) {
                // Log the error or return an error response
                error_log("File upload error during update: " . $e->getMessage());
                return $this->respond([
                    'status' => 500,
                    'message' => 'Failed to upload new image: ' . $e->getMessage()
                ], 500);
            }
        } else {
            // No new image uploaded or it's invalid, keep the existing image URL
            $dataToUpdate['image_product'] = $existingProduct[0]->image_product;
        }

        // 6. Save the updated data to the database
        // Assuming your `ProductModel`'s `save` method can handle updates based on primary key
        // or you have an `update` method like `$model->update($productId, $dataToUpdate)`
        if ($model->update($productId, $dataToUpdate)) { // Using update() method explicitly
            return $this->respond([
                'status' => 200,
                'message' => 'Product updated successfully',
                'data' => $dataToUpdate
            ]);
        } else {
            // Check for validation errors if using framework's validation
            $errors = $model->errors();
            if (!empty($errors)) {
                return $this->respond([
                    'status' => 400,
                    'message' => 'Validation failed',
                    'errors' => $errors
                ], 400);
            }
            return $this->respond([
                'status' => 500,
                'message' => 'Failed to update product'
            ], 500);
        }
    }

    public function deleteProduct($id) {
        $model = new ProductModel();
        $product = $model->where('id_product', $id)->first();
        
        if(!empty($product)) {
            $productPath = $product['filepath_product'].$product['filename_product'];
            $model->where('id_product',$id)->delete();
            unlink($productPath);
            return redirect()->to(env('app_clientBaseURL'));
        } else {
            return $this->respond(['status'=>404, 'message'=>'Order tidak ditemukan!'] );
        }


    }
}

    //  return $this->respond($existingProduct[0]->image_product);