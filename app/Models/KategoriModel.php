<?php

namespace App\Models;

use CodeIgniter\Model;

class KategoriModel extends Model
{

    protected $table                = 'kategori';
    protected $primaryKey           = 'id_kategori';

    protected $allowedFields        = [
        'judul',
        'gambar',
        'slug'
    ];


    function findDataById($id = null)
    {
        $data = $this
            ->asArray()
            ->where(['id_kategori' => $id])
            ->first();
        return $data;
    }
}
