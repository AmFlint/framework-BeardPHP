<?php

namespace Helper;


abstract class Model
{
    protected $db;
    protected $qb;

    public function __construct()
    {
        $this->db = DB::get();
        $this->qb = new QueryBuilder();
    }

}