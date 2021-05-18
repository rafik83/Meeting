<?php


namespace Proximum\Vimeet\Application\ThirdParty\CCIP;


use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\User;

class OrderCCIPView
{
    /** @var Order */
    public $order;

    /** @var User */
    public $user;

    public string $email;

    public string $gender;

    public string $firstname;

    public string $lastname;

    public string $address;

    public string $postcode;

    public string $city;

    public string $country;

    public string $phone;

    public int $product_id;

    public string $product_name;

    public int $quantity;

    public float $vat;

    public string $label;

    public float $price;

    public string $password;

    public string $paymentNumber;

    public string $captureToken;

    public function __construct(
        Order $order,
        User $user,
        string $email,
        string $gender,
        string $firstname,
        string $lastname,
        string $address,
        string $postcode,
        string $city,
        string $country,
        string $phone,
        int $product_id,
        string $product_name,
        int $quantity,
        float $vat,
        string $label,
        float $price,
        string $password,
        string $captureToken,
        string $paymentNumber
    ) {
        $this->order = $order;
        $this->user = $user;
        $this->email = $email;
        $this->gender = $gender;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->address = $address;
        $this->postcode = $postcode;
        $this->city = $city;
        $this->country = $country;
        $this->phone = $phone;
        $this->product_id = $product_id;
        $this->product_name = $product_name;
        $this->quantity = $quantity;
        $this->vat = $vat;
        $this->label = $label;
        $this->price = $price;
        $this->password = $password;
        $this->captureToken = $captureToken;
        $this->paymentNumber = $paymentNumber;
    }
}
