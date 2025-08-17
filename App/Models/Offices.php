<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as DB;


class Offices extends DB
{
    protected $table = "oficinas";
    protected $guarded = ["id", "created_at", "updated_at"];

    //Metodo que trae todas las columnas de la tabla
    public function OfficeUsers(){
        return $this->hasMany(OfficeUser::class, "id_oficina", "id");
    }
    public function getColumns()
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
}
