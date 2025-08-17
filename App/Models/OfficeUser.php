<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as DB;


class OfficeUser extends DB
{
    protected $table = "usuario_oficina";
    protected $guarded = ["id", "created_at", "updated_at"];

    //metodo para crear una relacion
    public function User(){
        return $this->belongsTo(Users::class, "id_usuario", "id");
    }
    public function Office(){
        return $this->belongsTo(Offices::class, "id_oficina", "id");
    }
    //Metodo que trae todas las columnas de la tabla
    public function getColumns()
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
}
