<?php

namespace SimpleCheckout;

use SimpleCheckout\Database\Model;

class Product extends Model {
    protected $table = 'products';
    protected $primaryKey = 'id';
}