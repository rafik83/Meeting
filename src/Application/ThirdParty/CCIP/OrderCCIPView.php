<?php


namespace Proximum\Vimeet\Application\ThirdParty\CCIP;


use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\User;

class OrderCCIPView
{
    /** @var Order[] */
    public array $order;

    public User $user;
    public string $email;
    public string $gender;
    public string $firstname;
    public string $lastname;
    public string $address;
    public string $postcode;
    public string $city;
    public string $country;
    public string $phone;
    public string $ccipProductIdsMap;
    public string $productName;
    public int $quantity;
    public float $vat;
    public string $label;
    public float $price;
    public string $password;
    public string $paymentNumber;
    public string $captureToken;

    public function __construct(
        array $orders,
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
        array $ccipProductIdsMap,
        string $productName,
        int $quantity,
        float $vat,
        string $label,
        float $price,
        string $password,
        string $captureToken,
        string $paymentNumber
    ) {
        $this->orders = $orders;
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
        $this->ccipProductIdsMap = $ccipProductIdsMap;
        $this->productName = $productName;
        $this->quantity = $quantity;
        $this->vat = $vat;
        $this->label = $label;
        $this->price = $price;
        $this->password = $password;
        $this->captureToken = $captureToken;
        $this->paymentNumber = $paymentNumber;
    }
}
