<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as DB;


class Users extends DB
{
    protected $table = "main_users";
    protected $guarded = ["id", "created_at", "updated_at"];

    public function scopewithAll($q)
    {
        $q->with(["Company", "Permissions", "Country", "State"]);
    }
    //metodo para crear una relacion
    public function Company()
    {
        return $this->belongsTo(Company::class, "company_id", "id");
    }
    //Metodo que trae todas las columnas de la tabla
    public function getColumns()
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
}
