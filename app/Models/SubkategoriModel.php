<?php

namespace App\Models;

use CodeIgniter\Model;

class SubkategoriModel extends Model



{
    protected $table                = 'subkategori';
    protected $primaryKey           = 'id_subkategori';
    protected $useAutoIncrement     = true;
    protected $returnType           = 'object';
    protected $allowedFields        = ['judul', 'gambar', 'slug', 'id_kategori'];

    function getAll()
    {
        $builder = $this->db->table('kategori');
        $builder->select(
            'kategori.id_kategori,
             kategori.judul as judul_kategori,
             subkategori.id_subkategori as id_subkategori,
             subkategori.gambar as gambar_subkategori,
             subkategori.slug as slug_subkategori,
             subkategori.judul as judul_subkategori,
             subkategori.id_kategori as id_kategori'
        );
        $builder->join('subkategori', 'subkategori.id_kategori = kategori.id_kategori');
        // dd($builder->get()->getResult());
        return $builder->get()->getResult();
    }
    function getbySlug($slug)
    {
        $builder = $this->db->table('subkategori');
        $builder->select(
            'subkategori.id_subkategori as id_subkategori,
             subkategori.gambar as gambar_subkategori,
             subkategori.slug as slug_subkategori,
             subkategori.judul as judul_subkategori,
             subkategori.id_kategori as id_kategori'
        );
        // $builder->join('kategori', 'kategori.id_k = subkategori.id_s', 'left');
        return $builder->where('slug', $slug)->get()->getRowObject();
    }
    function findDataById($id = null)
    {
        $data = $this
            ->asArray()
            ->where(['id_subkategori' => $id])
            ->first();
        return $data;
    }
}
