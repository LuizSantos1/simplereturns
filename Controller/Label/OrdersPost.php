<?php
/**
 * OrdersPost.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Aurora Extensions EULA,
 * which is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package       AuroraExtensions_SimpleReturns
 * @copyright     Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license       Aurora Extensions EULA
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Controller\Label;

use AuroraExtensions\SimpleReturns\{
    Exception\ExceptionFactory,
    Model\Adapter\Sales\Order as OrdersModel,
    Model\ViewModel\Orders as ViewModel,
    Shared\Action\Redirector,
    Shared\ModuleComponentInterface
};
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\{
    App\Action\Action,
    App\Action\Context,
    App\Action\HttpPostActionInterface,
    App\Request\DataPersistorInterface,
    Controller\Result\Redirect as ResultRedirect,
    Data\Form\FormKey\Validator as FormKeyValidator,
    Exception\LocalizedException
};

class OrdersPost extends Action implements
    HttpPostActionInterface,
    ModuleComponentInterface
{
    /** @see AuroraExtensions\SimpleReturns\Shared\Action\Redirector */
    use Redirector {
        Redirector::__initialize as protected;
    }

    /** @property CustomerRepositoryInterface $customerRepository */
    protected $customerRepository;

    /** @property DataPersistorInterface $dataPersistor */
    protected $dataPersistor;

    /** @property ExceptionFactory $exceptionFactory */
    protected $exceptionFactory;

    /** @property FormKeyValidator $formKeyValidator */
    protected $formKeyValidator;

    /** @property OrdersModel $ordersModel */
    protected $ordersModel;

    /** @property ViewModel $viewModel */
    protected $viewModel;

    /**
     * @param Context $context
     * @param CustomerRepositoryInterface $customerRepository
     * @param DataPersistorInterface $dataPersistor
     * @param ExceptionFactory $exceptionFactory
     * @param FormKeyValidator $formKeyValidator
     * @param OrdersModel $ordersModel
     * @param ViewModel $viewModel
     * @return void
     */
    public function __construct(
        Context $context,
        CustomerRepositoryInterface $customerRepository,
        DataPersistorInterface $dataPersistor,
        ExceptionFactory $exceptionFactory,
        FormKeyValidator $formKeyValidator,
        OrdersModel $ordersModel,
        ViewModel $viewModel
    ) {
        parent::__construct($context);
        $this->__initialize();
        $this->customerRepository = $customerRepository;
        $this->dataPersistor = $dataPersistor;
        $this->exceptionFactory = $exceptionFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->ordersModel = $ordersModel;
        $this->viewModel = $viewModel;
    }

    /**
     * Execute returns_label_ordersPost POST action.
     *
     * @return Redirect
     */
    public function execute()
    {
        /** @var Magento\Framework\App\RequestInterface $request */
        $request = $this->getRequest();

        if ($request->isPost() && $this->formKeyValidator->validate($request)) {
            /** @var array|null $params */
            $params = $request->getPost('returns');

            if ($params !== null) {
                /** @var string|null $email */
                $email = !empty($params['email']) ? $params['email'] : null;

                /** @var string|int|null $orderId */
                $orderId = !empty($params['order_id']) ? $params['order_id'] : null;

                /** @var string|int|null $zipCode */
                $zipCode = !empty($params['zip_code']) ? $params['zip_code'] : null;

                try {
                    if ($email !== null && $zipCode !== null) {
                        /** @var array $data */
                        $data = [
                            'email'      => $email,
                            'zip_code'   => $zipCode,
                            'is_checked' => true,
                        ];

                        $this->dataPersistor->set(self::DATA_PERSISTOR_KEY, $data);
                    } elseif ($orderId !== null && $zipCode !== null) {
                        /* Trim delivery route suffix from zip code. */
                        $zipCode = substr($zipCode, 0, 5);

                        /** @var array $data */
                        $data = [
                            'order_id'   => $orderId,
                            'zip_code'   => $zipCode,
                            'is_checked' => true,
                        ];

                        $this->dataPersistor->set(self::DATA_PERSISTOR_KEY, $data);
                    } else {
                        throw $this->exceptionFactory->create(
                            LocalizedException::class,
                            __(self::ERROR_MISSING_URL_PARAMS)
                        );
                    }
                } catch (LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                }
            }
        }

        return $this->getRedirect(self::ROUTE_RETURNS_LABEL_ORDERS);
    }
}
