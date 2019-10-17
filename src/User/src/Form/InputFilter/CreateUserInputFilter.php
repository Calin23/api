<?php

declare(strict_types=1);

namespace Api\User\Form\InputFilter;

use Api\App\Common\Message;
use Api\User\Entity\UserEntity;
use Zend\Filter\StringTrim;
use Zend\Filter\StripTags;
use Zend\InputFilter\CollectionInputFilter;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterAwareTrait;
use Zend\InputFilter\InputFilterInterface;
use Zend\Validator\Identical;
use Zend\Validator\InArray;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

/**
 * Class CreateUserInputFilter
 * @package Api\User\Form\InputFilter
 */
class CreateUserInputFilter implements InputFilterAwareInterface
{
    use InputFilterAwareTrait;

    const PASSWORD_MIN_LENGTH = 6;

    /**
     * @return InputFilterInterface
     */
    public function getInputFilter()
    {
        if (empty($this->inputFilter)) {
            $rolesInputFilter = new InputFilter();
            $rolesInputFilter->add([
                'name' => 'uuid',
                'required' => true,
                'filters' => [
                    ['name' => StringTrim::class],
                    ['name' => StripTags::class]
                ],
                'validators' => [
                    [
                        'name' => NotEmpty::class,
                        'break_chain_on_failure' => true,
                        'options' => [
                            'message' => Message::VALIDATOR_REQUIRED_FIELD
                        ]
                    ]
                ]
            ]);

            $rolesCollection = new CollectionInputFilter();
            $rolesCollection->setInputFilter($rolesInputFilter);
            $rolesCollection->setIsRequired(true);

            $this->inputFilter = new InputFilter();
            $this->inputFilter->add([
                'name' => 'email',
                'required' => true,
                'filters' => [
                    ['name' => StringTrim::class],
                    ['name' => StripTags::class]
                ],
                'validators' => [
                    [
                        'name' => NotEmpty::class,
                        'break_chain_on_failure' => true,
                        'options' => [
                            'message' => 'Email is required and cannot be empty!'
                        ]
                    ]
                ]
            ])->add([
                'name' => 'password',
                'required' => true,
                'filters' => [
                    ['name' => StringTrim::class],
                    ['name' => StripTags::class]
                ],
                'validators' => [
                    [
                        'name' => StringLength::class,
                        'options' => [
                            'min' => self::PASSWORD_MIN_LENGTH
                        ]
                    ], [
                        'name' => Identical::class,
                        'options' => [
                            'token' => 'passwordConfirm'
                        ]
                    ], [
                        'name' => NotEmpty::class,
                        'break_chain_on_failure' => true,
                        'options' => [
                            'message' => 'Password is required and cannot be empty!'
                        ]
                    ]
                ]
            ])->add([
                'name' => 'passwordConfirm',
                'required' => true,
                'filters' => [
                    ['name' => StringTrim::class],
                    ['name' => StripTags::class]
                ],
                'validators' => [
                    [
                        'name' => StringLength::class,
                        'options' => [
                            'min' => self::PASSWORD_MIN_LENGTH
                        ]
                    ],
                    [
                        'name' => Identical::class,
                        'options' => [
                            'token' => 'password'
                        ]
                    ]
                ]
            ])->add([
                'name' => 'status',
                'required' => false,
                'filters' => [
                    ['name' => StringTrim::class],
                    ['name' => StripTags::class]
                ],
                'validators' => [
                    [
                        'name' => InArray::class,
                        'break_chain_on_failure' => true,
                        'options' => [
                            'haystack' => UserEntity::STATUSES,
                            'message' => sprintf(Message::INVALID_VALUE, 'status')
                        ]
                    ]
                ]
            ])->add($rolesCollection, 'roles')->add((new CreateDetailInputFilter())->getInputFilter(), 'detail');
        }

        return $this->inputFilter;
    }
}
