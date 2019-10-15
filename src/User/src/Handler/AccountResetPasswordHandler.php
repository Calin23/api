<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Common\Message;
use Api\App\RestDispatchTrait;
use Api\User\Entity\UserEntity;
use Api\User\Entity\UserResetPasswordEntity;
use Api\User\Form\InputFilter\ResetPasswordInputFilter;
use Api\User\Form\InputFilter\UpdateUserInputFilter;
use Api\User\Service\UserService;
use Dot\AnnotatedServices\Annotation\Inject;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Hal\HalResponseFactory;
use Zend\Expressive\Hal\ResourceGenerator;
use Zend\Http\Response;

use function sprintf;

/**
 * Class AccountResetPasswordHandler
 * @package Api\User\Handler
 */
class AccountResetPasswordHandler implements RequestHandlerInterface
{
    use RestDispatchTrait;

    /** @var UserService $userService */
    protected $userService;

    /**
     * AccountHandler constructor.
     * @param HalResponseFactory $halResponseFactory
     * @param ResourceGenerator $resourceGenerator
     * @param UserService $userService
     *
     * @Inject({HalResponseFactory::class, ResourceGenerator::class, UserService::class})
     */
    public function __construct(
        HalResponseFactory $halResponseFactory,
        ResourceGenerator $resourceGenerator,
        UserService $userService
    ) {
        $this->responseFactory = $halResponseFactory;
        $this->resourceGenerator = $resourceGenerator;
        $this->userService = $userService;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function get(ServerRequestInterface $request) : ResponseInterface
    {
        $hash = $request->getAttribute('hash') ?? null;
        $user = $this->userService->findByResetPasswordHash($hash);
        if (!($user instanceof UserEntity)) {
            return $this->notFoundResponse(
                sprintf(Message::RESET_PASSWORD_NOT_FOUND, $hash)
            );
        }

        /** @var UserResetPasswordEntity $resetPaswordRequest */
        $resetPaswordRequest = $user->getResetPasswords()->current();
        if (!$resetPaswordRequest->isValid()) {
            return $this->errorResponse(sprintf(Message::RESET_PASSWORD_EXPIRED, $hash));
        }
        if ($resetPaswordRequest->isCompleted()) {
            return $this->errorResponse(sprintf(Message::RESET_PASSWORD_USED, $hash));
        }

        return $this->infoResponse(sprintf(Message::RESET_PASSWORD_VALID, $hash));
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function patch(ServerRequestInterface $request) : ResponseInterface
    {
        $hash = $request->getAttribute('hash') ?? null;
        $user = $this->userService->findByResetPasswordHash($hash);
        if (!($user instanceof UserEntity)) {
            return $this->notFoundResponse(
                sprintf(Message::RESET_PASSWORD_NOT_FOUND, $hash)
            );
        }

        /** @var UserResetPasswordEntity $resetPaswordRequest */
        $resetPaswordRequest = $user->getResetPasswords()->current();
        if (!$resetPaswordRequest->isValid()) {
            return $this->errorResponse(sprintf(Message::RESET_PASSWORD_EXPIRED, $hash));
        }
        if ($resetPaswordRequest->isCompleted()) {
            return $this->errorResponse(sprintf(Message::RESET_PASSWORD_USED, $hash));
        }

        $inputFilter = (new UpdateUserInputFilter())->getInputFilter();
        $inputFilter->setData($request->getParsedBody());
        if (!$inputFilter->isValid()) {
            return $this->errorResponse($inputFilter->getMessages());
        }

        try {
            $this->userService->updateUser(
                $resetPaswordRequest->markAsCompleted()->getUser(),
                $inputFilter->getValues()
            );
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        try {
            $this->userService->sendResetPasswordCompletedMail($user);
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        return $this->responseFactory->createResponse($request,
            $this->resourceGenerator->fromObject($resetPaswordRequest, $request)
        );
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function post(ServerRequestInterface $request) : ResponseInterface
    {
        $inputFilter = (new ResetPasswordInputFilter())->getInputFilter();
        $inputFilter->setData($request->getParsedBody());
        if (!$inputFilter->isValid()) {
            return $this->errorResponse($inputFilter->getMessages());
        }

        $user = $this->userService->findOneBy(['email' => $inputFilter->getValue('email')]);
        if (!($user instanceof UserEntity)) {
            return $this->notFoundResponse(
                sprintf(Message::USER_NOT_FOUND_BY_EMAIL, $inputFilter->getValue('email'))
            );
        }

        try {
            $user = $this->userService->updateUser($user->createResetPassword());
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        try {
            $this->userService->sendResetPasswordRequestedMail($user);
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        return $this->infoResponse(
            sprintf(Message::MAIL_SENT_RESET_PASSWORD, $user->getEmail()),
            Response::STATUS_CODE_201
        );
    }
}