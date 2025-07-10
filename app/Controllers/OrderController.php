<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\OrderModel;

helper('text');


class OrderController extends BaseController {

    public function generateRandomOrderString($length = 10, $prefix = 'order_') {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $prefix . $randomString;
    }
    public function createOrder() {
        $model = new OrderModel();

        $validationRules = [
            'id_order' => 'is_unique[orders.id_order]',
            'name_user' => 'required',
            'id_product' => 'required',
            'name_product' => 'required',
            'price_product' => 'required',
            'quantity_product' => 'required',
            'total_price' => 'required'
        ];

        if (!$this->validate($validationRules)) {
            return response()->setJSON(["status" => 401, "message" => $this->validator->getErrors()]);
        }

        $id_order = $this->generateRandomOrderString(10);
        // $id_order = $this->request->getPost('id_order');
        $name_user = $this->request->getPost('name_user');
        $id_product = $this->request->getPost('id_product');
        $name_product = $this->request->getPost('name_product');
        $price_product = $this->request->getPost('price_product');
        $quantity_product = $this->request->getPost('quantity_product');
        $total_price = $this->request->getPost('total_price');

        $data = [
            'id_order' => $id_order,
            'name_user' => $name_user,
            'id_product' => $id_product,
            'name_product' => $name_product,
            'price_product' => $price_product,
            'quantity_product' => $quantity_product,
            'total_price' => $total_price,
        ];

        if ($model->save($data)) {
            return response()->setJSON(['status' => 200, 'message' => 'Pesanan berhasil dibuat.', 'datas' => $data]);
        } else {
            return response()->setJSON(['status' => 500, 'message' => 'Gagal membuat pesanan.']);
        }
    }
    public function getUserOrders($id) {
        $model = new OrderModel();
        $orders = $model->where('name_user',$id)->findAll();

        if (!empty($orders)) {
            return response()->setJSON(['status' => 200, 'message' => 'Pesanan ditemukan!.', 'datas' => $orders]);
        } else {
            return response()->setJSON(['status' => 404, 'message' => 'Tidak ada pesanan!.']);
        }
    }

    public function getOrders() {
        $model = new OrderModel();
        $orders = $model->findAll();

        if(!empty($orders)) {
            return response()->setJSON( ['status' => 200, 'message'=> 'Pesanan ditemukan!.', 'datas' => $orders]);
        } else {
            return response()->setJSON( ['status' => 404, 'message' => 'Tidak ada pesanan!.']);
        }
    }
 
    public function deleteOrder($id) {
        $model = new OrderModel();
        $order = $model->where('id_order',$id)->get()->getResult();

        if(!empty($order)) {
            $model->where('id_order',$id)->delete();
            return response()->setJSON(['status'=>200, 'message'=>'Pesanan berhasil dihapus!']);
        } else {
            return response()->setJSON( ['status' => 404, 'message' => 'Tidak ada pesanan!.']);
        }
    }

    public function processOrder($id) {
        $model = new OrderModel();
        $order = $model->where('id_order',$id)->first();

        if(!empty($order)) {
            $model->query("UPDATE orders SET status_order = 'onprocess' WHERE id_order = '$id'");
            return response()->setJSON(['status'=>200, 'message'=>'Pesanan akan diproses!']);
        } else {
            return response()->setJSON( ['status' => 404, 'message' => 'Tidak ada pesanan!.']);
        }
    }

    public function doneOrder($id) {
        $model = new OrderModel();
        $order = $model->where('id_order',$id)->first();

        if(!empty($order)) {
            $model->query("UPDATE orders SET status_order = 'done' WHERE id_order = '$id'");
            return response()->setJSON(['status'=>200, 'message'=>'Pesanan selesai diproses!']);
        } else {
            return response()->setJSON( ['status' => 404, 'message' => 'Tidak ada pesanan!.']);
        }
    }
}
