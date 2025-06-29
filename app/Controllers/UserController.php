<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;
use CodeIgniter\HTTP\Request;
helper('cookie');

class UserController extends BaseController
{
    use ResponseTrait;
    public function getUsers() {

        $model = new UserModel();
        $users = $model->findAll();

        return $this->respond($users);
    }
    public function getUser($id) {
        $model = new UserModel();
        $user = $model->where('name_user',$id)->get()->getResult();

        if (!empty($user)) {
            return $this->respond(data: ['status' => 200, 'message' => 'Pengguna ditemukan!.', 'user' => $user]);
        } else {
            return $this->failServerError('Pengguna tidak ditemukan!.');
        }
    }

    public function register()
    {
        $model = new UserModel();
        
        $validationRules = [
            'name' => 'required|min_length[3]|max_length[50]|is_unique[users.name_user]',
            'email' => 'required|valid_email|is_unique[users.email_user]',
            'password' => 'required|min_length[6]'
        ];

        if (!$this->validate($validationRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $name = $this->request->getPost('name');
        $email = $this->request->getPost('email');
        $password_plain = $this->request->getPost('password');
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
                'islogin',
                'true',
                [
                    'expires' => time() + (86400 * 30),
                    'path' => '/',
                ]
            );
            setcookie(
                'username',
                $name, // Menggunakan $name yang sudah bersih dari POST
                [
                    'expires' => time() + (86400 * 30), // 30 hari
                    'path' => '/',
                ]
            );
            setcookie(
                'sessionkey',
                $user_token, // Menggunakan $name yang sudah bersih dari POST
                [
                    'expires' => time() + (86400 * 30), // 30 hari
                    'path' => '/',
                ]
            );

            // 6. Respon Sukses
            // return $this->respond(data: ['status' => 200, 'message' => 'Pengguna berhasil terdaftar!']);
            return redirect()->to(env('app_clientBaseURL'));
        } else {
            // Jika penyimpanan gagal (misalnya karena error database)
            return $this->failServerError('Gagal menyimpan data pengguna. Silakan coba lagi.');
        }

    }

    public function login() {
        $model = new UserModel();
        
        $validationRules = [
            'email' => 'required|valid_email',
            'password' => 'required|min_length[6]'
        ];

        if (!$this->validate($validationRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $email = $this->request->getPost('email');
        $passwordPlain = $this->request->getPost('password');

        $user = $model->where('email_user', $email)->first();

        if(!empty($user)) {
            $passwordHash = $user['password_user'];

            if(password_verify($passwordPlain, $passwordHash)) {
                
                setcookie(
                'islogin',
                'true',
                [
                    'expires' => time() + (86400 * 30),
                    'path' => '/',
                ]
                );
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
                return $this->respond(['status'=>200, 'message'=>'Login Succesfully!', 'cookiesdata'=>['username'=>$user["name_user"], 'sessionkey' => $user["token_user"]]]);

            } else {
                return $this->respond(['status'=>401, 'message'=>'Wrong password!']);
            }
            
        } else {
            return $this->respond(['status'=>404, 'message'=>'User not found!']);
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
        return redirect()->to(env('app_clientBaseURL'));   
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
                    return $this->respond(['status'=>200, 'isAdmin'=>true]);
                }
                return $this->respond(['status'=>200, 'isAdmin'=>false]);
            } else {
                return $this->respond(['status'=>401, 'message'=>'Session invalid, sessionkey doesnt match!']);
            }
        } else {
            return $this->respond(['status'=>401, 'message'=>'Session invalid, user not found!']);
        }
    }
}

// return $this->respond($passwordHash);