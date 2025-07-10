<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProductModel;

class ProductController extends BaseController {
    public function getProducts() {   

        $model = new ProductModel();
        $products = $model->findAll();

        if(!empty($products)) {
            return response()->setJSON(['status'=>200, 'message'=>'Berhasil mengambil data', 'datas'=>$products]);
        }
        return response()->setJSON(['status'=>404, 'message'=>'Tidak ada produk']);
    }

    public function addProduct() {
        $model = new ProductModel();

        $validationRules = [
            'name' => 'is_unique[products.name_product]',
        ];

        if (!$this->validate($validationRules)) {
            return response()->setJSON(["status" => 401, "message" => $this->validator->getErrors()]);
        }

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
                return response()->setJSON(['status'=>200, 'message'=>'Berhasil menambahkan produk']);
            } 
            return response()->setJSON(['status'=>500, 'message'=>'Gagal menambahkan produk']);
        }
        return response()->setJSON(['status'=>500, 'message'=>'Produk harus memiliki gambar']);
    }
    
    public function updateProduct(){
        $model = new ProductModel();

        $productId = $this->request->getPost('id');
        $existingProduct = $model->where('id_product', $productId)->first();
        
        if (!$existingProduct) {
            return response()->setJSON([
                'status' => 404,
                'message' => 'Produk tidak ditemukan.'
            ], 404);
        }

        $name = $this->request->getPost('name');
        $price = $this->request->getPost('price');
        $stock = $this->request->getPost('stock');

        $name == '' ? $nameFinal = $existingProduct['name_product'] : $nameFinal = $name;
        $price == '' ? $priceFinal = $existingProduct['price_product'] : $priceFinal = $price;
        $stock == '' ? $stockFinal = $existingProduct['stock_product'] : $stockFinal = $stock;

        $dataToUpdate = [
            // 'name_product' => $name ?? $existingProduct['name_product'], 
            // 'price_product' => $price ?? $existingProduct['price_product'],
            // 'stock_product' => $stock ?? $existingProduct['stock_product']
            'name_product' => $nameFinal,
            'price_product' => $priceFinal,
            'stock_product' => $stockFinal
        ];

        $newFile = $this->request->getFile('image');

        if ($newFile && $newFile->isValid() && !$newFile->hasMoved()) {
            $oldImageFileName = basename($existingProduct['image_product']);

            $newFileName = $newFile->getRandomName();
            $newFilePath = FCPATH . 'files\\images\\';
            $newFileUrl = base_url() . 'files/images/' . $newFileName;

            try {
                $newFile->move(FCPATH . 'files/images/', $newFileName);

                $dataToUpdate['image_product'] = $newFileUrl;
                $dataToUpdate['filename_product'] = $newFileName;
                $dataToUpdate['filepath_product'] = $newFilePath;
                $oldImagePath = FCPATH . 'files/images/' . $oldImageFileName;
                if (!empty($oldImageFileName) && file_exists($oldImagePath) && $oldImageFileName !== $newFileName) {
                    unlink($oldImagePath);
                }

            } catch (\Exception $e) {
                error_log("File upload error during update: " . $e->getMessage());
                return response()->setJSON([
                    'status' => 500,
                    'message' => 'Gagal update gambar produk yang baru.: ' . $e->getMessage()
                ], 500);
            }
        } else {
            $dataToUpdate['image_product'] = $existingProduct['image_product'];
        }

        if ($model->update($productId, $dataToUpdate)) {
            return response()->setJSON([
                'status' => 200,
                'message' => 'Produk berhasil diupdate.',
                'data' => $dataToUpdate
            ]);
        } else {
            $errors = $model->errors();
            if (!empty($errors)) {
                return response()->setJSON([
                    'status' => 400,
                    'message' => 'Validasi gagal!',
                    'errors' => $errors
                ], 400);
            }
            return response()->setJSON([
                'status' => 500,
                'message' => 'Gagal mengupdate produk.'
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
            return response()->setJSON(['status'=>200, 'message'=>'Berhasil menghapus produk']);
        } else {
            return response()->setJSON(['status'=>404, 'message'=>'Produk tidak ditemukan!'] );
        }


    }
}

    //  return $this->respond($existingProduct[0]->image_product);