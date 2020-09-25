<?php


namespace AppBundle\Controller;


use AppBundle\Domain\Payment;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends Controller
{
    private $payment;


    public function __construct()
    {
        $this->payment = new Payment();
    }

    /**
     * @Route("/payment/form", name="paymentForm")
     * @return string
     */
    public function paymentForm(Request $request)
    {
        $requestString = $request->getQueryString();

        $parameters = array();
        $parameters = $this->getListParameters($requestString);

        $isValidSessionId = $this->payment->checkSessionId($parameters['sessionId']);

        if (!$isValidSessionId) {
            return new Response(
                json_encode(array('message' => 'Время жизни платежной сессии истекло'), JSON_UNESCAPED_UNICODE),
                Response::HTTP_OK
            );
        }

        return $this->render(
            'payment/index.html.twig', array(
                'paymentAmount' => $parameters['paymentAmount']/100,
                'purposePayment' => $parameters['purposePayment'],
                'sessionId' => $parameters['sessionId']
            )
        );
    }

    /**
     * @Route("/payment/send", name="paymentSend")
     * @return Response
     */
    public function paymentSend(Request $request)
    {
        $data = $request->request;
        $isValidSessionId = $this->payment->checkSessionId($data->get('sessionId'));

        if (!$isValidSessionId) {
            return new Response(
                json_encode(array('message' => 'Время жизни платежной сессии истекло'), JSON_UNESCAPED_UNICODE),
                Response::HTTP_OK
            );
        }

        $result = $this->payment->checkDataForPayment($data);
        if (!empty($result)) {
            return new Response(
                json_encode($result, JSON_UNESCAPED_UNICODE),
                Response::HTTP_CONFLICT //todo - Подобрать правильный код ответа
            );
        }

        $storePaymentMessage = $this->payment->savePayment($request);

        return new Response(
            json_encode($storePaymentMessage, JSON_UNESCAPED_UNICODE),
            Response::HTTP_OK
        );
    }

    private function getListParameters($requestString)
    {
        $parametersHash = explode('&', $requestString);

        $parameters = array();
        foreach ($parametersHash as $value) {
            $arrKeyValue = array();
            $arrKeyValue = explode('=', $value);
            $parameters[$arrKeyValue[0]] = $arrKeyValue[1];
        }

        return $parameters;
    }

}
