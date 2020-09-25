<?php


namespace AppBundle\Infrastructure;

use AppBundle\Domain\PaymentStore;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;

class FileStorePayment implements PaymentStore
{
    const PATH_TO_STORE_FILE = '../var/storeForPayment.txt';

    public function find(array $parameters)
    {
        // TODO: Implement find() method.
        $result = [];

        return $result;
    }

    public function findOne($paymentId)
    {
        // TODO: Implement findOne() method.
        $result = [];

        return $result;
    }

    public function save($parameters)
    {
        $resultMessage = array();

        $stringForSave = $parameters['timePayment'] . '#' .
            $parameters['sessionId'] . '#' .
            $parameters['paymentAmount'] . '#' .
            $parameters['purposePayment'] . '#' .
            $parameters['cardNumber'] . '#' .
            $parameters['cardHolder'];

        try {
            if (!is_readable(self::PATH_TO_STORE_FILE)) {
                if (!file_put_contents(self::PATH_TO_STORE_FILE, PHP_EOL, FILE_APPEND)) {
                    throw new AccessDeniedException(self::PATH_TO_STORE_FILE);
                }
            }

            $fileHandle = fopen(self::PATH_TO_STORE_FILE, "a+");
            if ($fileHandle) {
                fwrite($fileHandle, $stringForSave . PHP_EOL);
                fclose($fileHandle);

                $resultMessage = array(
                    'isSuccessStore' => true,
                    'message' => 'Сохранения параметров платежа произведено успешно'
                );
            } else {
                $resultMessage = array(
                    'isSuccessStore' => false,
                    'message' => 'При сохранение параметров платежа произошла ошибка. Данные не сохранены'
                );
            }

        } catch (\Exception $e) {
            $resultMessage = array(
                'isSuccessStore' => false,
                'message' => $e->getMessage()
            );
        }

        return $resultMessage;
    }

}
