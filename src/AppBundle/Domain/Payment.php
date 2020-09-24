<?php


namespace AppBundle\Domain;


use AppBundle\Infrastructure\FileStore;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\VarDumper\VarDumper;

class Payment
{
    const QUANTITY_DIGIT_IN_CARDNUMBER = 16;

    private $store;

    public function __construct()
    {
        $this->store = new FileStore();
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

        //todo - проверка строки "Назначение платежа"
        $purposePayment = $data->get('purposePayment');
        $isValidPurposePayment = $this->checkPurposePayment($purposePayment);
        if (!$isValidPurposePayment) {
            $errorMessage['purposePayment']['empty'] = 'Не указано назначение платежа';
        }

        //todo - проверка "Суммы платежа"
        $paymentAmount = $data->get('paymentAmount');
        $isValidPaymentAmount = $this->checkPaymentAmount($paymentAmount);
        if (!$isValidPaymentAmount) {
            $errorMessage['paymentAmount']['empty'] = 'Не указана сумма платежа';
        }

        //todo - проверка "Номера карточки"
        $cardNumber = $data->get('cardHolder');
        $isValidCardNumber = false;

        if (empty($cardNumber)) {
            $errorMessage['cardNumber']['empty'] = 'Не указан номер банковской карточки';
        }
        if (!$this->checkCardNumber($cardNumber)) {
            $errorMessage['cardNumber']['number'] = 'Номер карты не проходит проверку';
        }

        //todo - проверка "Срока действия карты"
        $isValidCardDate = $this->checkExpireDate($data);

        if (!$isValidCardDate) {
            $errorMessage['ExpireDate']['message'] = 'Срок годности карты не проходит проверку';
        }

        //todo - проверка строки "Владелец карты"
        $isValidCardHolder = false;
        $cardHolder = $data->get('cardHolder');

        if (empty($cardHolder)) {
            $errorMessage['cardHolder']['empty'] = 'Не указан владелец карты';
        }

        //todo - проверка строки "CVV"
        $isValidCVV = false;
        $CVV = $data->get('CVV');

        if (empty($CVV)) {
            $errorMessage['CVV']['empty'] = 'Не указан CVV-код';
        }

        //todo - проверка всех присланных параметров
        $errorMessage = ($isValidPurposePayment && $isValidPaymentAmount && $isValidCardNumber);

        //todo - проверка всех присланных параметров

        VarDumper::dump($errorMessage); die('&&&&&&&&&&&');
        return $errorMessage;
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

        VarDumper::dump((int) $monthExpire);
        die(__METHOD__);

        //todo - проверка месяца на длину строки и правильность 01-12
        if (strlen($monthExpire) < 2 || (int) $monthExpire < 1 || (int) $monthExpire > 12) {
            $isValidCardDate = false;
        }

        //todo - проверка года на длину строки и правильность 20-99
        $currentYear = date('y', time());
        if (strlen($yearExpire) < 2 || (int) $yearExpire < $currentYear) {
            $isValidCardDate = false;
        }

        return $isValidCardDate;
    }

    private function checkPurposePayment($purposePayment)
    {
        //todo - Проверка назначения платежа
        return empty($purposePayment) ? false : true;
    }

    private function checkPaymentAmount($paymentAmount)
    {
        //todo - Проверка суммы платежа
        return empty($paymentAmount) ? false : true;
    }

}
