<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
helper('cookie');

class UserController extends BaseController {
    public function getUsers() {

        $model = new UserModel();
        $users = $model->findAll();

        if(!empty($users)) {
            return response()->setJSON(['status'=>200, 'message'=>'Users ditemukan!', 'datas'=>$users]);
        }
        return response()->setJSON(['status'=>404, 'message'=>'Users tidak ditemukan!']);

    }
    public function getUser($id) {
        $model = new UserModel();
        $user = $model->where('name_user',$id)->first();

        if (!empty($user)) {
            return response()->setJSON( ['status' => 200, 'message' => 'User ditemukan!', 'datas' => $user]);
        } else {
            return response()->setJSON(['status'=>404, 'message'=>'User tidak ditemukan!']);
        }
    }

    public function register()
    {
        $model = new UserModel();
        
        $validationRules = [
            'name' => 'required|min_length[3]|max_length[50]|is_unique[users.name_user]',
            'email' => 'required|valid_email|is_unique[users.email_user]',
            'password' => 'required|min_length[6]',
            'passwordConfirm' => 'required|min_length[6]'
        ];

        if (!$this->validate($validationRules)) {
            return response()->setJSON(['status'=>401 , 'message'=>$this->validator->getErrors()]);
        }

        $name = $this->request->getPost('name');
        $email = $this->request->getPost('email');
        $password_plain = $this->request->getPost('password');
        $password_confirm = $this->request->getPost('passwordConfirm');

        if($password_plain != $password_confirm) {
            return response()->setJSON(['status'=>401, 'message'=>'Password dan Konfirmasi Password tidak sama!']);
        }

        $user_token = strtoupper(uniqid(mt_rand()).uniqid(mt_rand()));


        $password_hashed = password_hash($password_plain, PASSWORD_BCRYPT);

        $data = [
            'name_user' => $name,
            'email_user' => $email,
            'password_user' => $password_hashed,
            'token_user' => $user_token
        ];

        if ($model->save($data)) {

            setcookie(
                'username',
                $name,
                [
                    'expires' => time() + (86400 * 30),
                    'path' => '/',
                ]
            );
            setcookie(
                'sessionkey',
                $user_token,
                [
                    'expires' => time() + (86400 * 30), // 30 hari
                    'path' => '/',
                ]
            );
            return response()->setJSON(['status'=>200, 'message'=>'Register Succesfully!', 'cookiesdata'=>['username'=>$name, 'sessionkey' => $user_token]]);
        } else {
            // Jika penyimpanan gagal (misalnya karena error database)
            return response()->setJSON(['status'=>500, 'message'=>'Error while saving user!']);
        }

    }

    public function login() {
        $model = new UserModel();
        
        $validationRules = [
            'email' => 'required|valid_email',
            'password' => 'required|min_length[6]'
        ];

        if (!$this->validate($validationRules)) {
            return response()->setJSON(['status'=>401, 'message'=>'Validasi gagal silahkan coba lagi!']);
        }

        $email = $this->request->getPost('email');
        $passwordPlain = $this->request->getPost('password');

        $user = $model->where('email_user', $email)->first();

        if(!empty($user)) {
            $passwordHash = $user['password_user'];

            if(password_verify($passwordPlain, $passwordHash)) {
                
                setcookie(
                    'username',
                    $user["name_user"],
                    [
                        'expires' => time() + (86400 * 30), // 30 hari
                        'path' => '/',
                    ]
                );
                setcookie(
                    'sessionkey',
                    $user["token_user"],
                    [
                        'expires' => time() + (86400 * 30), // 30 hari
                        'path' => '/',
                    ]
                );
                return response()->setJSON(['status'=>200, 'message'=>'Login Succesfully!', 'cookiesdata'=>['username'=>$user["name_user"], 'sessionkey' => $user["token_user"]]]);

            } else {
                return response()->setJSON(['status'=>401, 'message'=>'Validasi gagal silahkan coba lagi!']);
            }
            
        } else {
            return response()->setJSON(['status'=>404, 'message'=>'Validasi gagal silahkan coba lagi!']);
        }
    }

    public function logout() {
        setcookie(
        'islogin',
        '',
        [
            'expires' => 0,
            'path' => '/',
        ]
        );
        setcookie(
        'username',
        '',
        [
            'expires' => 0,
            'path' => '/',
        ]
        );
        setcookie(
        'sessionkey',
        '',
        [
            'expires' => 0,
            'path' => '/',
        ]
        );
        return response()->setJSON(['status'=>200, 'message'=>'Logout Succesfully!']);;   
    }

    public function sessionValidation() {
        $model = new UserModel();

        $username = $this->request->getPost('username');
        $sessionkey = $this->request->getPost('sessionkey');
        
        $user = $model->where('name_user', $username)->first();
        if(!empty($user)) {
            $token = $user['token_user'];
            if($token == $sessionkey) {
                if($user['is_admin']) {
                    return response()->setJSON(['status'=>200, 'message'=>'verified as admin', 'isAdmin'=>true]);
                }
                return response()->setJSON(['status'=>200, 'message'=>'verified as user', 'isAdmin'=>false]);
            } else {
                return response()->setJSON(['status'=>401, 'message'=>'Session invalid, sessionkey doesnt match!']);
            }
        } else {
            return response()->setJSON(['status'=>404, 'message'=>'Session invalid, user not found!']);
        }
    }
}

// return $this->respond($passwordHash);