<?php

namespace App\Controllers;

use App\Models\KategoriModel;
use App\Models\SubkategoriModel;
use CodeIgniter\API\ResponseTrait;


class Subkategori extends BaseController
{
    use ResponseTrait;
    function __construct()
    {
        $this->kategoriModel = new KategoriModel();
        $this->subkategoriModel = new SubkategoriModel();
    }

    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return mixed
     */
    public function index()
    {
        $data['subkategori'] = $this->subkategoriModel->findAll();
        return $this->respond($data, 200);
    }

    /**
     * Return the properties of a resource object
     *
     * @return mixed
     */
    public function show($id = null)
    {

        $data = $this->subkategoriModel->findDataById($id);
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
     * Return a new resource object, with default properties
     *
     * @return mixed
     */
    // public function new()
    // {
    //     $data['kategori'] = $this->kategoriModel->findAll();
    //     $data['validation'] = \Config\Services::validation();
    //     echo view('dashboard/subkategori/add', $data);
    // }

    /**
     * Create a new resource object, from "posted" parameters
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
                'is_unique[subkategori.judul]',
                'errors' => [
                    'required' => '{field} Tidak Boleh Kosong',
                    'is_unique' => '{field} Sudah Ada'
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
                $imageFile->move('uploads/subkategori', $nameFile);
            } else {
                $nameFile = 'no-image.png';
            }
            $data = [
                'judul' => htmlspecialchars($this->request->getPost('judul')),
                'gambar' =>  $nameFile,
                'slug' => htmlspecialchars($this->request->getPost('slug')),
                'id_kategori' => htmlspecialchars($this->request->getPost('id_kategori'))
            ];
            $this->subkategoriModel->insert($data);
            $response = [
                'status' => 200,
                'error' => false,
                'message' => 'Data Berhasil Ditambah',
                'data' => $data
            ];
        }
        return $this->respondCreated($response);
    }

    /**
     * Add or update a model resource, from "posted" properties
     *
     * @return mixed
     */
    public function update($id = null)
    {
        $old = $this->subkategoriModel->findDataById($id);
        $rules = [
            'judul' => [
                'required',
                "is_unique[subkategori.judul,id_subkategori,{$id}]",
                'errors' => [
                    'required' => '{field} Tidak Boleh Kosong!',
                    'is_unique' => '{field} Sudah Ada!'
                ]
            ],
            'gambar' => [
                'mime_in[gambar,image/png,image/jpg,image/jpeg]',
                'errors' => [
                    'mime_in' => 'Extension tidak sesuai!'
                ]
            ]
        ];
        if (!$this->validate($rules)) {
            $response = [
                'error' => true,
                'message' => $this->validator->getErrors(),
                'data' => []
            ];
        }
        $imageFile = $this->request->getFile('gambar');
        if ($imageFile->getError() == 4) {
            $nameFile = $old['gambar'];
        } else {
            $nameFile = $imageFile->getRandomName();
            $imageFile->move('uploads/subkategori', $nameFile);
            //jika gambar default
            if ($old['gambar'] != 'no-image.png' &&  file_exists('uploads/subkategori/' . $old['gambar'])) {
                unlink('uploads/subkategori/' . $old['gambar']);
            }
        }
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
                'slug' => $this->request->getPost('slug'),
                'id_kategori' => $this->request->getPost('id_kategori')
            ];
        }
        $this->subkategoriModel->update($id, $data);
        $response = [
            'error' => false,
            'message' => 'Data berhasil Dirubah',
            'data' => $data
        ];

        return $this->respond($response);
    }

    /**
     * Delete the designated resource object from the model
     *
     * @return mixed
     */
    public function delete($id = null)
    {
        $data = $this->subkategoriModel->findDataById($id);
        if ($data) {
            $img = $data['gambar'];
            if ($img != 'no-image.png' && file_exists('uploads/subkategori/' . $img)) {
                unlink('uploads/subkategori/' . $img);
            }
            $this->subkategoriModel->delete($id);
            $response = [
                'messages' => 'Data Dihapus'
            ];
            return $this->respondDeleted($response);
        } else {
            return $this->failNotFound('Tidak Ditemukan Data Dengan ID:' . $id);
        }
    }
}
