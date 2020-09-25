<?php


namespace AppBundle\Domain;


use AppBundle\Infrastructure\FileStorePayment;
use AppBundle\Infrastructure\FileStoreSession;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class Payment
{
    const QUANTITY_DIGIT_IN_CARDNUMBER = 16;

    private $store;
    private $storePayments;

    public function __construct()
    {
        $this->store = new FileStoreSession();
        $this->storePayments = new FileStorePayment();
    }

    public function checkSessionId($sessionId)
    {
        $result = false;

        $foundString = $this->store->findOne($sessionId);

        if (!empty($foundString) && $this->checkTimeValid($foundString)) {
            $result = true;
        }

        return $result;
    }

    public function checkDataForPayment(ParameterBag $data)
    {
        $errorMessage = array();

        $purposePayment = $data->get('purposePayment');
        $isValidPurposePayment = $this->checkPurposePayment($purposePayment);
        if (!$isValidPurposePayment) {
            $errorMessage['purposePayment']['empty'] = 'Не указано назначение платежа';
        }

        $paymentAmount = $data->get('paymentAmount');
        $isValidPaymentAmount = $this->checkPaymentAmount($paymentAmount);
        if (!$isValidPaymentAmount) {
            $errorMessage['paymentAmount']['empty'] = 'Не указана сумма платежа';
        }

        $cardNumber = $data->get('cardNumber1');
        $cardNumber .= $data->get('cardNumber2');
        $cardNumber .= $data->get('cardNumber3');
        $cardNumber .= $data->get('cardNumber4');
        $isValidCardNumber = $this->checkCardNumber($cardNumber);

        if (empty($cardNumber)) {
            $errorMessage['cardNumber']['empty'] = 'Не указан номер банковской карточки';
        }
        if (!$isValidCardNumber) {
            $errorMessage['cardNumber']['number'] = 'Номер карты не проходит проверку';
        }

        $isValidCardDate = $this->checkExpireDate($data);
        if (!$isValidCardDate) {
            $errorMessage['ExpireDate']['message'] = 'Срок годности карты не проходит проверку';
        }

        $cardHolder = $data->get('cardHolder');
        if (empty($cardHolder)) {
            $errorMessage['cardHolder']['empty'] = 'Не указан владелец карты';
        }

        $CVV = $data->get('CVV');
        if (empty($CVV) || strlen((int)$CVV) < 3 || strlen((int)$CVV) > 4 || (int)$CVV == 0) {
            $errorMessage['CVV']['isValid'] = 'Некорректный CVV-код';
        }

        return $errorMessage;
    }

    public function savePayment(Request $request)
    {
        $data = $request->request;

        $parameters = array();
        $parameters['timePayment'] = time();
        $parameters['sessionId'] = $data->get('sessionId');
        $parameters['paymentAmount'] = $data->get('paymentAmount') * 100;
        $parameters['purposePayment'] = $data->get('purposePayment');
        $parameters['cardNumber'] = $data->get('cardNumber1') . $data->get('cardNumber2') . $data->get('cardNumber3') . $data->get('cardNumber4');
        $parameters['cardHolder'] = $data->get('cardHolder');

        return $this->storePayments->save($parameters);
    }

    private function checkTimeValid($foundString)
    {
        $isValid = false;
        $arr = explode('#', $foundString);

        if ((int) $arr[1] >= time()) {
            $isValid = true;
        }

        return $isValid;
    }

    private function checkCardNumber($cardNumber)
    {
        $result = false;

        $numberWOHyphen = strrev(preg_replace('/[^\d]+/', '', $cardNumber));

        if (strlen($numberWOHyphen) == self::QUANTITY_DIGIT_IN_CARDNUMBER ) {
            $summaryAllDigit = 0;
            for ($i = 0, $j = strlen($numberWOHyphen); $i < $j; $i++) {
                if (($i % 2) == 0) {
                    $valueDigit = $numberWOHyphen[$i];
                } else {
                    $valueDigit = $numberWOHyphen[$i] * 2;
                    if ($valueDigit > 9)  {
                        $valueDigit -= 9;
                    }
                }
                $summaryAllDigit += $valueDigit;
            }
            $result = ($summaryAllDigit % 10) === 0;
        }
        return $result;
    }

    private function checkExpireDate(ParameterBag $data)
    {
        $isValidCardDate = true;
        $monthExpire = $data->get('monthExpire');
        $yearExpire = $data->get('yearExpire');

        if (strlen($monthExpire) < 2 || (int) $monthExpire < 1 || (int) $monthExpire > 12) {
            $isValidCardDate = false;
        }

        $currentYear = date('y', time());
        if (strlen($yearExpire) < 2 || (int) $yearExpire < $currentYear) {
            $isValidCardDate = false;
        }

        return $isValidCardDate;
    }

    private function checkPurposePayment($purposePayment)
    {
        return empty($purposePayment) ? false : true;
    }

    private function checkPaymentAmount($paymentAmount)
    {
        return empty($paymentAmount) ? false : true;
    }

}
