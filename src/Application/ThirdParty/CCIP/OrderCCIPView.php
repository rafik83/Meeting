<?php


namespace Proximum\Vimeet\Application\ThirdParty\CCIP;


use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class OrderCCIPView
{
    public int $transactionId;
    public DateTimeInterface $date;
    public Sheet $sheet;
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
    public array $ccipProductIdsMap;
    public string $locale;
    public string $password;
    public string $paymentNumber;
    public string $captureToken;

    public function __construct(
        int $transactionId,
        DateTimeInterface $date,
        Sheet $sheet,
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
        string $locale,
        string $password,
        string $captureToken,
        string $paymentNumber
    ) {
        $this->transactionId = $transactionId;
        $this->date = $date;
        $this->sheet = $sheet;
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
        $this->locale = $locale;
        $this->password = $password;
        $this->captureToken = $captureToken;
        $this->paymentNumber = $paymentNumber;
    }
}
