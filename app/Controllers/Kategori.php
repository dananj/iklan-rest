<?php

namespace App\Controllers;

use App\Models\KategoriModel;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\SubkategoriModel;

class Kategori extends ResourceController
{

    use ResponseTrait;
    function __construct()
    {
        $this->kategoriModel = new KategoriModel();
        $this->SubkategoriModel = new SubkategoriModel();
    }

    /**
     * Present a view of resource objects
     *
     * @return mixed
     */
    public function index()
    {
        $data['kategori'] = $this->kategoriModel->findAll();
        return $this->respond($data, 200);
    }

    /**
     * Present a view to present a specific resource object
     *
     * @param mixed $id
     *
     * @return mixed
     */
    public function show($id = null)
    {
        $data = $this->kategoriModel->findDataById($id);
        if ($data) {
            $response = [
                'status' => 200,
                'error' => false,
                'message' => "Data Ditemukan",
                'data' => $data
            ];
            return $this->respond($response, 200);
        } else {
            $response = [
                'status' => 500,
                'error' => true,
                'message' => "Data Tidak Ditemukan",
                'data' => []
            ];
            return $this->respond($response);
        }
    }

    /**
     * Present a view to present a new single resource object
     *
     * @return mixed
     */
    public function new()
    {
        // session();
        // $data = [
        //     'validation' => \Config\Services::validation()
        // ];
        // echo view('dashboard/kategori/add', $data);
        // $data = [
        //     'judul' => $this->request->getPost('judul'),
        //     'gambar' =>  $this->request->getPost('gambar'),
        //     'slug' => $this->request->getPost('slug')

        // ];
        // $data = json_decode(file_get_contents("php://input"));

        // $this->kategoriModel->insert($data);
        // $response = [
        //     'status'   => 201,
        //     'error'    => null,
        //     'messages' => [
        //         'success' => 'Data Saved'
        //     ]
        // ];
        // return $this->respondCreated($data, 201);
    }

    /**
     * Process the creation/insertion of a new resource object.
     * This should be a POST.
     *
     * @return mixed
     */

    public function create()
    {
        $rules = [
            'gambar' => [
                'mime_in[gambar,image/png,image/jpg,image/jpeg]',
                'errors' => [
                    'mime_in' => 'Extension tidak sesuai!'
                ]
            ],
            'judul' => [
                'required',
                "is_unique[kategori.judul]",
                'errors' => [
                    'required' => '{field} Harus Diisi!',
                    'is_unique' => '{field} Sudah Ada!'
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
            $imageFile = $this->request->getFile('gambar');

            if ($imageFile->isValid()) {
                //upload  ke public folder
                $nameFile = $imageFile->getRandomName();
                $imageFile->move('uploads/kategori', $nameFile);
            } else {
                $nameFile = 'no-image.png';
            }
            $data = [
                'judul' => htmlspecialchars($this->request->getPost('judul')),
                'gambar' =>  $nameFile,
                'slug' => htmlspecialchars($this->request->getPost('slug'))
            ];
            $this->kategoriModel->insert($data);
            $response = [
                'status' => 200,
                'error' => false,
                'message' => 'Data Berhasil Ditambah',
                'data' => [$data]
            ];
        }

        return $this->respondCreated($response);
    }


    /**
     * Process the updating, full or partial, of a specific resource object.
     * This should be a POST.
     *
     * @param mixed $id
     *
     * @return mixed
     */
    public function update($id = null)
    {

        $old = $this->kategoriModel->findDataById($id);
        $rules = [
            'judul' => [
                "is_unique[kategori.judul,id_kategori,{$id}]",
                "errors" => [
                    "required" => "{field}Tidak boleh kosong!",
                    "is_unique" => "{field}Sudah Ada!"
                ]

            ],
        ];
        if (!$this->validate($rules)) {
            $response = [
                'error' => true,
                'message' => $this->validator->getErrors(),
                'data' => []
            ];
        } else {
            //image
            $imageFile = $this->request->getFile('gambar');
            $imageOld = $old['gambar'];
            if ($imageFile->getError() == 4) {
                $nameFile = $imageOld;
            } else {
                $nameFile = $imageFile->getRandomName();
                $imageFile->move('uploads/kategori', $nameFile);
                //jika gambar default
                if ($imageOld != 'no-image.png' && file_exists('uploads/kategori/' . $nameFile)) {
                    unlink('uploads/kategori/' . $imageOld);
                }
            }
            //end image
            $json = $this->request->getJSON();
            if ($json) {

                $data = [
                    'judul' => $json->judul,
                    'gambar' => $json->gambar,
                    'slug' => $json->slug
                ];
            } else {

                $data = [
                    'judul' => $this->request->getPost('judul'),
                    'gambar' => $nameFile,
                    'slug' => $this->request->getPost('slug')
                ];
            }
            $this->kategoriModel->update($id, $data);
            $response = [
                'error' => false,
                'message' => 'Data berhasil Dirubah',
                'data' => $data
            ];
        }
        return $this->respond($response);
    }
    /**
     * Process the deletion of a specific resource object
     *
     * @param mixed $id
     *
     * @return mixed
     */
    public function delete($id = null)
    {
        $data = $this->kategoriModel->findDataById($id);
        if ($data) {
            $img = $data['gambar'];
            if ($img != 'no-image.png' && file_exists('uploads/kategori/' . $img)) {
                unlink('uploads/kategori/' . $img);
            }
            $this->kategoriModel->delete($id);
            $response = [
                'messages' => 'Data Dihapus'
            ];
            return $this->respondDeleted($response);
        } else {
            return $this->failNotFound('Tidak Ditemukan Data Dengan ID:' . $id);
        }
    }
}
