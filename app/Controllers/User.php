<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\Response;
use CodeIgniter\RESTful\ResourceController;
use Firebase\JWT\JWT;
use CodeIgniter\API\ResponseTrait;

class User extends ResourceController
{

    use ResponseTrait;
    function __construct()
    {
        $this->userModel = new UserModel();
    }
    public function index()
    {
        $user = $this->userModel->findAll();
        return $this->Respond($user);
    }
    public function register()
    {
        $rules = [
            'nama' => [
                'required',
                'errors' => [
                    'required' => '*nama tidak boleh kosong!'
                ]
            ],
            'username' => [
                'required',
                'errors' => [
                    'required' => '*username tidak boleh kosong!'
                ]
            ],
            'password' => [
                'required',
                'errors' => [
                    'required' => '*password tidak boleh kosong!'
                ]
            ],
            'verify_password' => [
                'required',
                'matches[password]',
                'errors' => [
                    'required' => '*password tidak boleh kosong!',
                    'matches' => '*password tidak sama'
                ]
            ],
            'hp' => [
                'required',
                'errors' => [
                    'required' => '*no hp tidak boleh kosong!'
                ]
            ],
            'email' => [
                'required',
                'valid_email',
                'is_unique[user.email]',
                'errors' => [
                    'required' => '*email tidak boleh kosong!',
                    'valid_email' => '*email tidak valid',
                    'is_unique' => '*email telah digunakan'
                ]
            ],
            'alamat' => [
                'required',
                'errors' => [
                    'required' => '*alamat tidak boleh kosong!'
                ]
            ],
        ];



        if (!$this->validate($rules)) {
            $response = [
                'status' => 500,
                'error' => true,
                'message' => $this->validator->getErrors(),
                'data' => []
            ];
        } else {
            $hashp = password_hash(htmlspecialchars($this->request->getVar("password")), PASSWORD_BCRYPT);
            $data = [
                'nama' => $this->request->getVar('nama'),
                'username' => $this->request->getVar('username'),
                'email' => $this->request->getVar('email'),
                'hp' => $this->request->getVar('hp'),
                'alamat' => $this->request->getVar('alamat'),
                'password' => $hashp,
                'role' => 'user',
            ];

            if ($this->userModel->insert($data)) {
                $response = [
                    'status' => 200,
                    "error" => false,
                    'messages' => 'Successfully, user has been registered',
                    'data' => $data
                ];
            } else {
                $response = [
                    'status' => 500,
                    "error" => true,
                    'messages' => 'Failed to create user',
                    'data' => []
                ];
            }
        }

        return $this->respondCreated($response);
    }
    private function getKey()
    {
        return "my_application_secret";
    }

    public function login()
    {
        helper(['form']);
        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required|min_length[1]'
        ];
        if (!$this->validate($rules)) return $this->fail($this->validator->getErrors());
        $model = new UserModel();
        $user = $model->where("email", $this->request->getVar('email'))->first();
        if (!$user) return $this->failNotFound('Email Not Found');

        $verify = password_verify($this->request->getVar('password'), $user['password']);
        if (!$verify) return $this->fail('Wrong Password');

        $key = $this->getKey();
        $payload = array(
            "iat" => 1356999524,
            "nbf" => 1357000000,
            "uid" => $user['id'],
            "email" => $user['email']
        );

        $token = JWT::encode($payload, $key);

        return $this->respond($token);
        // helper(['form']);
        // $rules = [
        //     "email" => "required|valid_email|min_length[6]",
        //     "password" => "required",
        // ];

        // $messages = [
        //     "email" => [
        //         "required" => "Email required",
        //         "valid_email" => "Email address is not in format"
        //     ],
        //     "password" => [
        //         "required" => "password is required"
        //     ],
        // ];

        // if (!$this->validate($rules, $messages)) {

        //     $response = [
        //         'status' => 500,
        //         'error' => true,
        //         'message' => $this->validator->getErrors(),
        //         'data' => []
        //     ];

        //     return $this->respondCreated($response);
        // } else {

        //     $userdata = $this->userModel->where("email", $this->request->getVar("email"))->first();
        //     // $pass = password_hash("ade", PASSWORD_BCRYPT);
        //     if (!empty($userdata)) {
        //         if (password_verify($this->request->getVar('password'), $userdata['password'])) {

        //             $key = $this->getKey();

        //             $iat = time(); // current timestamp value
        //             $nbf = $iat + 10;
        //             $exp = $iat + 3600;

        //             $payload = array(
        //                 "iss" => "The_claim",
        //                 "aud" => "The_Aud",
        //                 "iat" => $iat, // issued at
        //                 "nbf" => $nbf, //not before in seconds
        //                 "exp" => $exp, // expire time in seconds
        //                 "data" => $userdata,
        //             );

        //             $token = JWT::encode($payload, $key);

        //             $response = [
        //                 'status' => 200,
        //                 'error' => false,
        //                 'messages' => 'User logged In successfully',
        //                 'data' => [
        //                     'token' => $token
        //                 ]
        //             ];
        //             return $this->respondCreated($response);
        //         } else {

        //             $response = [
        //                 'status' => 500,
        //                 'error' => true,
        //                 'messages' => 'Incorrect details',
        //                 'data' => []
        //             ];
        //             return $this->respondCreated($response);
        //         }
        //     } else {
        //         $response = [
        //             'status' => 500,
        //             'error' => true,
        //             'messages' => 'User not found',
        //             'data' => []
        //         ];
        //         return $this->respondCreated($response);
        //     }
        // }
    }
}
