<?php


namespace AppBundle\Controller;


use AppBundle\Domain\Payment;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\VarDumper\VarDumper;

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
            return $this->render('payment/non_valid_sessionId.html.twig');
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
            return $this->render('payment/non_valid_sessionId.html.twig');
        }

        $result = $this->payment->checkDataForPayment($data);

        if (!empty($result)) {
            foreach ($result as $item) {
                var_dump($item);
            }
        }
        die('********');
//        VarDumper::dump($data); die('********');
        return new Response(
            __METHOD__,
            Response::HTTP_OK
        );
    }

    private function getListParameters($requestString)
    {
        //todo - разбор строки параметров
        $parametersHash = explode('&', $requestString);

        $parameters = array();
        foreach ($parametersHash as $value) {
            $arr = array();
            $arr = explode('=', $value);
            $parameters[$arr[0]] = $arr[1];
        }

        return $parameters;
    }

}
