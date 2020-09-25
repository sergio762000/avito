<?php


namespace AppBundle\Domain;


interface PaymentStore
{
    public function find(array $parameters);

    public function findOne($paymentId);

    public function save($parameters);

}
