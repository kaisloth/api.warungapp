<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\API\ResponseTrait;
use App\Models\OrderModel;
use App\Models\OrderDataModel;

helper('text');
        // Add CORS headers manually


class OrderController extends BaseController
{

    use ResponseTrait;
    public function createOrder() {

        header('Access-Control-Allow-Origin: *'); // Or specify your frontend origin: 'http://localhost:5173'
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS'); // Or just GET if that's all you need
        header('Access-Control-Allow-Headers: Content-Type, Authorization'); // Add any headers your frontend sends

        $model = new OrderModel();

        $validationRules = [
            'id_order' => 'required|is_unique[orders.id_order]',
            'name_user' => 'required',
            'id_product' => 'required',
            'name_product' => 'required',
            'price_product' => 'required',
            'quantity_product' => 'required',
            'total_price' => 'required'
        ];

        if (!$this->validate($validationRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $id_order = $this->request->getPost('id_order');
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
            return $this->respond(data: ['status' => 200, 'message' => 'Pesanan berhasil dibuat.', 'order' => $data]);
        } else {
            return $this->failServerError('Pesanan gagal dibuat.');
        }
    }
    public function getUserOrders($id) {
        $model = new OrderModel();
        $orders = $model->where('name_user',$id)->get()->getResult();

        if (!empty($orders)) {
            return $this->respond(data: ['status' => 200, 'message' => 'Order ditemukan!.', 'order' => $orders]);
        } else {
            return $this->failServerError('Order tidak ditemukan!.');
        }
    }

    public function getOrders() {
        $model = new OrderModel();
        $orders = $model->findAll();

        if(!empty($orders)) {
            return $this->respond(data: ['status' => 200, 'orders' => $orders]);
        } else {
            return $this->failServerError('Order tidak ditemukan!.');
        }
    }

    public function deleteOrder($id) {
        $model = new OrderModel();
        $order = $model->where('id_order',$id)->get()->getResult();

        if(!empty($order)) {
            $model->where('id_order',$id)->delete();
            return redirect()->to(env('app_clientBaseURL').'/orders');
        } else {
            return $this->failServerError('Order tidak ditemukan!.');
        }
    }

    public function processOrder($id) {
        $model = new OrderModel();
        $order = $model->where('id_order',$id)->get()->getResult();

        if(!empty($order)) {
            $model->query("UPDATE orders SET status_order = 'onprocess' WHERE id_order = '$id'");
            return redirect()->to(env('app_clientBaseURL').'/orders');
        } else {
            return $this->failServerError('Order tidak ditemukan!.');
        }
    }

    public function doneOrder($id) {
        $model = new OrderModel();
        $order = $model->where('id_order',$id)->get()->getResult();

        if(!empty($order)) {
            $model->query("UPDATE orders SET status_order = 'done' WHERE id_order = '$id'");
            return redirect()->to(env('app_clientBaseURL').'/orders');
        } else {
            return $this->failServerError('Order tidak ditemukan!.');
        }
    }
}
