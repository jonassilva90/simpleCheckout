<?php

namespace SimpleCheckout;

use SimpleCheckout\Database\Model;

class Product extends Model {
    protected $table = 'produtos';
    protected $primaryKey = 'id';
}