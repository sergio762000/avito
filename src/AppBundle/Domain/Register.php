<?php


namespace AppBundle\Domain;


use AppBundle\Infrastructure\FileStoreSession;

class Register
{

    private $store;

    public function __construct()
    {
        $this->store = new FileStoreSession();
    }

    public function createSessionUID()
    {
        $sequenceBand = array(4,2,2,2,6);

        $sessionUID = '';
        $i = 0;
        try {
            while ($i <= count($sequenceBand) - 1) {
                $sessionUID .= (string) bin2hex(random_bytes($sequenceBand[$i]));
                $i++;
                $sessionUID .= ($i < count($sequenceBand)) ? "-" : "";
            }
        } catch (\Exception $e) {
            return json_encode(array("message" => $e->getMessage()));
        }

        return $sessionUID;
    }

    public function saveSessionId($sessionUID, $urlForNotification)
    {
        $validUntil = (string) (time() + 1800);

        $dataForStore = array();
        $dataForStore['sessionUID'] = $sessionUID;
        $dataForStore['urlForNotification'] = $urlForNotification;
        $dataForStore['validUntil'] = $validUntil;

        return $this->store->save($dataForStore);
    }

    public function checkSentData($data)
    {
        $check = false;

        if (is_object($data)) {
            $isCheckAmount = (int)$data->paymentAmount > 0;
            $isCheckPurpose = (empty($data->purposePayment)) ? false : true;
            $check = ($isCheckAmount && $isCheckPurpose);
        }

        return $check;
    }

}
