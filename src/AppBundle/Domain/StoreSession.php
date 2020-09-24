<?php


namespace AppBundle\Domain;


interface StoreSession
{
    public function find();

    public function findOne($uid);

    public function save(array $dataForSave);

    public function delete($uid);
}