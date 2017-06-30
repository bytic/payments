<?php

namespace ByTIC\Payments\Controllers\Traits;

use ByTIC\Common\Records\Record;
use ByTIC\Omnipay\Common\Message\Traits\HtmlResponses\ConfirmHtmlTrait;
use ByTIC\Omnipay\Common\Message\Traits\RedirectHtmlTrait;
use ByTIC\Payments\Gateways\Manager as GatewaysManager;
use ByTIC\Payments\Gateways\Providers\AbstractGateway\Message\Traits\CompletePurchaseResponseTrait;
use ByTIC\Payments\Gateways\Providers\AbstractGateway\Message\Traits\HasModelProcessedResponse;
use ByTIC\Payments\Models\Purchase\Traits\IsPurchasableModelTrait;
use Nip\Records\RecordManager;
use Nip\Request;
use Omnipay\Common\Message\AbstractResponse;

/**
 * Class PurchaseControllerTrait
 * @package ByTIC\Common\Payments\Controllers\Traits
 *
 * @method IsPurchasableModelTrait checkItem
 */
trait PurchaseControllerTrait
{

    public function redirectToPayment()
    {
        $model = $this->getModelFromRequest();
        $request = $model->getPurchaseRequest();
        /** @var RedirectHtmlTrait $response */
        $response = $request->send();
        $response->getView()->set('subtitle', $model->getPurchaseName());
        $response->getView()->set('item', $model);
        $response->getView()->set('response', $model);
        echo $response->getRedirectResponse()->getContent();
        die();
    }

    /**
     * @param bool|array $key
     * @return Record|IsPurchasableModelTrait
     */
    abstract protected function getModelFromRequest($key = false);

    public function confirm()
    {
        $response = $this->getConfirmActionResponse();
        $model = $response->getModel();
        if ($model) {
            $response->processModel();
        }
        $this->confirmProcessResponse($response);
        $response->send();
        die();
    }

    /**
     * @return CompletePurchaseResponseTrait|ConfirmHtmlTrait
     */
    protected function getConfirmActionResponse()
    {
        /** @var CompletePurchaseResponseTrait $response */
        $response = GatewaysManager::detectItemFromHttpRequest(
            $this->getModelManager(),
            'completePurchase',
            $this->getRequest()
        );

        if (($response instanceof AbstractResponse) === false) {
            $this->dispatchAccessDeniedResponse();
        }
        return $response;
    }

    /**
     * @return RecordManager
     */
    protected abstract function getModelManager();

    /**
     * @return Request
     */
    abstract protected function getRequest();

    abstract protected function dispatchAccessDeniedResponse();

    /**
     * @param CompletePurchaseResponseTrait $response
     * @return void
     */
    abstract protected function confirmProcessResponse($response);

    public function ipn()
    {
        $response = $this->getIpnActionResponse();
        $model = $response->getModel();
        if ($model) {
            $response->processModel();
        }
        $this->ipnProcessResponse($response);
        $response->send();
        die();
    }

    /**
     * @return AbstractResponse|HasModelProcessedResponse
     */
    protected function getIpnActionResponse()
    {
        /** @var AbstractResponse|HasModelProcessedResponse $response */
        $response = GatewaysManager::detectItemFromHttpRequest(
            $this->getModelManager(),
            'serverCompletePurchase',
            $this->getRequest()
        );

        if (($response instanceof AbstractResponse) === false) {
            $this->dispatchAccessDeniedResponse();
        }
        return $response;
    }

    /**
     * @param AbstractResponse $response
     * @return void
     */
    abstract protected function ipnProcessResponse($response);

    /**
     * @return GatewaysManager
     */
    protected function getGatewaysManager()
    {
        return GatewaysManager::instance();
    }
}
