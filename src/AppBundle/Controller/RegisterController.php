<?php


namespace AppBundle\Controller;

use AppBundle\Domain\Register;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends Controller
{
    private $register;

    public function __construct()
    {
        $this->register = new Register();
    }

    /**
     * @Route("/register")
     * @param Request $request
     * @return Response
     */
    public function registerAction(Request $request)
    {
        $content = $request->getContent();
        $data = json_decode($content);

        $isValidSentData = $this->register->checkSentData($data);

        if (!$isValidSentData) {
            return new Response(
                json_encode(array("message" => "Присланные данные некорректны!!!"), JSON_UNESCAPED_UNICODE),
                Response::HTTP_CONFLICT //todo - Подобрать правильный код ответа
            );
        }

        $sessionId = $this->register->createSessionUID();
        $isValidSave = $this->register->saveSessionId($sessionId, $data->notificationUrl);

        if (!$isValidSave) {
            return new Response(
                json_encode(array("message" => "Ошибка регистрации сессии")),
                Response::HTTP_CONFLICT //todo - Подобрать правильный код ответа
            );
        }

        $paymentAmount = 'paymentAmount=' . $data->paymentAmount;
        $purposePayment = 'purposePayment=' . $data->purposePayment;

        $urlPayment = 'http://localhost:8888/payment/form?sessionId=';
        $urlPayment .= $sessionId . '&' . $paymentAmount . '&' . $purposePayment;
        $linkPayment['urlForPayment'] = $urlPayment;

        return new Response(
            json_encode($linkPayment, JSON_UNESCAPED_SLASHES),
            Response::HTTP_CREATED
        );

    }

}
