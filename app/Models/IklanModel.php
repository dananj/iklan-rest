<?php

namespace App\Models;

use CodeIgniter\Model;

class IklanModel extends Model
{

    protected $table                = 'iklan';
    protected $primaryKey           = 'id_iklan';
    protected $returnType           = 'object';
    protected $allowedFields        = ['judul', 'deskripsi', 'harga', 'slug', 'id_subkategori'];

    //join iklan kategori gambar    
    function joinTables()
    {
        $builder = $this->db->table('iklan');
        $builder->select('
            iklan.id_iklan as id_iklan,
            iklan.judul as judul_iklan,
            iklan.deskripsi as deskripsi_iklan,
            iklan.slug as slug_iklan,
            iklan.harga as harga_iklan,
            gambar.nama as list_gambar,
            gambar.id_gambar as id_gambar,
            kategori.judul as judul_kategori,
            kategori.slug as slug_kategori,
            subkategori.id_subkategori as id_subkategori,
            subkategori.judul as judul_subkategori,
            subkategori.slug as slug_subkategori,
        ');

        // $builder
        $builder->join('subkategori', 'subkategori.id_subkategori = iklan.id_subkategori');
        $builder->join('kategori', 'kategori.id_kategori = subkategori.id_kategori');
        $builder->join('gambar', 'gambar.id_iklan = iklan.id_iklan');
        return $builder;
    }
    function getAllData()
    {
        return $this->joinTables()->get()->getResult();
    }
    function findDataBySlug($slug = null)
    {
        $builder = $this->joinTables();
        return $builder->where('iklan.slug', $slug)->get()->getRowObject();
    }
    function getIdBySlug($slug = null)
    {
        $builder = $this->findDataBySlug($slug);
        return $builder;
    }
    function search($keyword)
    {
        $builder = $this->joinTables();
        $builder->like('iklan.deskripsi', $keyword);
        $builder->orLike('iklan.judul', $keyword);
        return $builder->get()->getResult();
    }
}
