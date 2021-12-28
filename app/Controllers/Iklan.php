<?php

namespace App\Controllers;

use App\Models\GambarModel;
use App\Models\IklanModel;
use App\Models\SubkategoriModel;
use CodeIgniter\API\ResponseTrait;

class Iklan extends BaseController
{
    use ResponseTrait;
    function __construct()
    {
        $this->iklanModel = new IklanModel();
        $this->subkategoriModel = new SubkategoriModel();
        $this->gambarModel = new GambarModel();
    }
    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return mixed
     */
    public function index()
    {
        $keyword = $this->request->getVar("keyword");
        if ($keyword) {
            $iklan = $this->iklanModel->search($keyword);
            if ($iklan) {
                $response = [
                    'status' => 200,
                    'error' => false,
                    'message' => "Data Ditemukan",
                    'data' => $iklan,
                    'keyword' => $keyword
                ];
            } else {
                $response = [
                    'message' => "Data Tidak Ditemukan",
                ];
            }
        } else {
            $iklan = $this->iklanModel->getAllData();

            if ($iklan) {
                $response = [
                    'status' => 200,
                    'error' => false,
                    'message' => "Data Ditemukan",
                    'data' => $iklan,
                    'keyword' => '$keyword',
                ];
            } else {
                $response = [
                    'message' => "Tidak Ada Iklan",
                ];
            }
        }
        return $this->respond($response);
    }
    /**
     * Return the properties of a resource object
     *
     * @return mixed
     */
    public function show($slug = null)
    {
        $data = $this->iklanModel->findDataBySlug($slug);
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
            return $this->respond($response, 500);
        }
    }

    /**
     * Return a new resource object, with default properties
     *
     * @return mixed
     */
    public function new()
    {
        $data['subkategori'] = $this->subkategoriModel->findAll();
        $data['validation'] = \Config\Services::validation();
        echo view('dashboard/iklan/add', $data);
    }

    /**
     * Create a new resource object, from "posted" parameters
     *
     * @return mixed
     */
    public function create()
    {
        $rules = [
            'judul' => [
                'required',
                'errors' => [
                    'required' => '*{field} tidak boleh kosong!'
                ]
            ],
            'harga' => [
                'required',
                'errors' => [
                    'required' => '*{field} tidak boleh kosong!'
                ]
            ],
            'deskripsi' => [
                'required',
                'errors' => [
                    'required' => '*{field} tidak boleh kosong!'
                ]
            ],
            'gambar' => [
                'uploaded[gambar]',
                'mime_in[gambar,image/png,image/jpg,image/jpeg]',
                'errors' => [
                    'uploaded' => '*{field} tidak boleh kosong!',
                    'mime_in' => '*Extension tidak sesuai,Masukan PNG,JPG,JPEG!'
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
            //insert iklan
            $slug =  $this->request->getPost('slug') . '-' . rand('001', '999');
            $data = [
                'judul' => $this->request->getPost('judul'),
                'deskripsi' => $this->request->getPost('deskripsi'),
                'slug' => $slug,
                'harga' => $this->request->getPost('harga'),
                'id_subkategori' => $this->request->getPost('id_subkategori')
            ];
            $this->iklanModel->insert($data);
            $img = array();
            foreach ($this->request->getFileMultiple('gambar') as $files) {
                if ($files->isValid()) {
                    $new_name =  $files->getRandomName();
                    array_push(
                        $img,
                        $new_name
                    );
                    $files->move('uploads/iklan/', $new_name);
                }
            }
            //ambil id_iklan
            $iklan = $this->iklanModel->where('slug', $slug)->first();

            $img = implode(',', $img);
            $data_img = [
                'nama' =>  $img,
                'id_iklan' => $iklan->id_iklan
            ];
            $this->gambarModel->insert($data_img);
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
    public function update($slug = null)
    {
        $rules = [
            'judul' => [
                'required',
                'errors' => [
                    'required' => '*Nama Iklan Tidak Boleh Kosong'
                ]
            ],
            'harga' => [
                'required',
                'errors' => [
                    'required' => '*Harga Tidak Boleh Kosong'
                ]
            ],
            'deskripsi' => [
                'required',
                'errors' => [
                    'required' => '*Deskripsi Tidak Boleh Kosong'
                ]
            ],
            'gambar' => [
                'mime_in[gambar,image/png,image/jpg,image/jpeg]',
                'errors' => [
                    'mime_in' => '*Extension tidak sesuai,Masukan PNG,JPG,JPEG!'
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

            //gambar lama sebelum edit
            $gambar_lama = array();
            foreach ($this->request->getPost('gambar_lama') as $oldImage) {
                array_push(
                    $gambar_lama,
                    $oldImage
                );
            }
            $files = $this->request->getFileMultiple('gambar');

            $img_baru = array();

            foreach ($files as $file) {
                $i = 0;
                if ($file->isValid()) {
                    $new_name =  $file->getName();
                    array_push(
                        $img_baru,
                        $new_name
                    );
                    if (file_exists('uploads/iklan/' . $gambar_lama[$i])) {
                        unlink('uploads/iklan/' . $gambar_lama[$i]);
                    }
                    $file->move('uploads/iklan/', $new_name);
                } else {
                    array_push(
                        $img_baru,
                        $gambar_lama[$i]
                    );
                }
                $i++;
            }
            $img =  implode(',', $img_baru);
            $id = $this->iklanModel->findDataBySlug($slug);
            $data_img = [
                'nama' =>  $img,
                'id_iklan' => $id->id_iklan
            ];
            $this->gambarModel->update($id->id_gambar, $data_img);

            $data = [
                'judul' => $this->request->getPost('judul'),
                'deskripsi' =>  $this->request->getPost('deskripsi'),
                'slug' => $this->request->getPost('slug'),
                'harga' => $this->request->getPost('harga'),
                'id_subkategori' => $this->request->getPost('id_subkategori')
            ];

            $this->iklanModel->update($id->id_iklan, $data);
            $response = [
                'error' => false,
                'message' => 'Data berhasil Dirubah',
                'data' => [$data, $data_img]
            ];
        }
        return $this->respond($response);
    }

    /**
     * Delete the designated resource object from the model
     *
     * @return mixed
     */
    public function delete($id = null)
    {
        $gambars = $this->gambarModel->where('id_iklan', $id)->first();
        if ($gambars) {
            $gambar_array = explode(",", $gambars->nama);
            foreach ($gambar_array as  $gambar) {
                if (file_exists('uploads/iklan/' . $gambar)) {
                    unlink('uploads/iklan/' . $gambar);
                }
            }
        }
        // dd($gambar_array);
        $this->iklanModel->where('id_iklan', $id)->delete();
        return redirect()->to(site_url('iklan'))->with('success', 'Iklan Berhasil Dihapus');
    }
}
