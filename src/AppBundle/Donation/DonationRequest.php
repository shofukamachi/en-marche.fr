<?php

namespace AppBundle\Donation;

use AppBundle\Entity\Adherent;
use AppBundle\Validator\PayboxSubscription as AssertPayboxSubscription;
use AppBundle\Validator\UnitedNationsCountry as AssertUnitedNationsCountry;
use AppBundle\ValueObject\Genders;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

class DonationRequest
{
    const DEFAULT_AMOUNT = 50.0;

    private $uuid;

    /**
     * @Assert\NotBlank(message="donation.amount.not_blank")
     * @Assert\GreaterThan(value=0, message="donation.amount.greater_than_0")
     * @Assert\LessThanOrEqual(value=7500, message="donation.amount.less_than_7500")
     */
    private $amount;

    /**
     * @Assert\NotBlank(message="common.gender.invalid_choice")
     * @Assert\Choice(
     *   callback = {"AppBundle\ValueObject\Genders", "all"},
     *   message="common.gender.invalid_choice",
     *   strict=true
     * )
     */
    public $gender;

    /**
     * @Assert\NotBlank(message="common.first_name.not_blank")
     * @Assert\Length(
     *   min=2,
     *   max=50,
     *   minMessage="common.first_name.min_length",
     *   maxMessage="common.first_name.max_length"
     * )
     */
    public $firstName;

    /**
     * @Assert\NotBlank(message="common.first_name.not_blank")
     * @Assert\Length(
     *   min=2,
     *   max=50,
     *   minMessage="common.last_name.min_length",
     *   maxMessage="common.last_name.max_length"
     * )
     */
    public $lastName;

    /**
     * @Assert\NotBlank(message="common.email.not_blank")
     * @Assert\Email(message="common.email.invalid")
     */
    private $emailAddress;

    /**
     * @Assert\NotBlank(message="common.address.required")
     * @Assert\Length(max=150, maxMessage="common.address.max_length")
     */
    private $address;

    /**
     * @Assert\NotBlank
     * @Assert\Length(max=15)
     */
    private $postalCode;

    /**
     * @Assert\Length(max=15)
     */
    private $city;

    /**
     * @Assert\Length(max=255)
     */
    private $cityName;

    /**
     * @Assert\NotBlank
     * @AssertUnitedNationsCountry(message="common.country.invalid")
     */
    private $country;

    /**
     * @AssertPhoneNumber(defaultRegion="FR")
     */
    private $phone;

    private $clientIp;

    /**
     * @AssertPayboxSubscription
     */
    private $duration;

    public function __construct(
        UuidInterface $uuid,
        string $clientIp,
        float $amount = self::DEFAULT_AMOUNT,
        int $duration = PayboxPaymentSubscription::NONE
    ) {
        $this->uuid = $uuid;
        $this->clientIp = $clientIp;
        $this->emailAddress = '';
        $this->country = 'FR';
        $this->setAmount($amount);
        $this->phone = static::createPhoneNumber();
        $this->duration = $duration;
    }

    public static function createFromAdherent(
        Adherent $adherent,
        string $clientIp,
        float $amount = self::DEFAULT_AMOUNT,
        int $duration = PayboxPaymentSubscription::NONE
    ): self {
        $dto = new self(Uuid::uuid4(), $clientIp, $amount, $duration);
        $dto->gender = $adherent->getGender();
        $dto->firstName = $adherent->getFirstName();
        $dto->lastName = $adherent->getLastName();
        $dto->emailAddress = $adherent->getEmailAddress();
        $dto->address = $adherent->getAddress();
        $dto->postalCode = $adherent->getPostalCode();
        $dto->city = $adherent->getCity();
        $dto->cityName = $adherent->getCityName();
        $dto->country = $adherent->getCountry();
        $dto->phone = $adherent->getPhone();

        return $dto;
    }

    public static function createFromGuest(string $clientIp, float $amount = 50.0): self
    {
        return new self(Uuid::uuid4(), $clientIp, $amount);
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount)
    {
        $this->amount = floor($amount * 100) / 100;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender)
    {
        $this->gender = $gender;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName)
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName)
    {
        $this->lastName = $lastName;
    }

    public function setEmailAddress(?string $emailAddress)
    {
        $this->emailAddress = mb_strtolower($emailAddress);
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address)
    {
        $this->address = $address;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode)
    {
        $this->postalCode = $postalCode;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city)
    {
        $this->city = $city;
    }

    public function getCityName(): ?string
    {
        return $this->cityName;
    }

    public function setCityName(?string $cityName)
    {
        $this->cityName = $cityName;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country)
    {
        $this->country = $country;
    }

    public function setPhone(?PhoneNumber $phone)
    {
        $this->phone = $phone;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function getClientIp(): string
    {
        return $this->clientIp;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): void
    {
        $this->duration = $duration;
    }

    public function retryPayload(array $payload): self
    {
        $retry = clone $this;

        if (isset($payload['ge']) && in_array($payload['ge'], Genders::ALL, true)) {
            $retry->gender = $payload['ge'];
        }

        if (isset($payload['ln'])) {
            $retry->lastName = (string) $payload['ln'];
        }

        if (isset($payload['fn'])) {
            $retry->firstName = (string) $payload['fn'];
        }

        if (isset($payload['em'])) {
            $retry->emailAddress = urldecode((string) $payload['em']);
        }

        if ($payload['co']) {
            $retry->country = (string) $payload['co'];
        }

        if (isset($payload['pc'])) {
            $retry->postalCode = (string) $payload['pc'];
        }

        if (isset($payload['ci'])) {
            $retry->cityName = (string) $payload['ci'];
        }

        if (isset($payload['ad'])) {
            $retry->address = urldecode((string) $payload['ad']);
        }

        if (isset($payload['phc']) && isset($payload['phn'])) {
            $retry->phone = self::createPhoneNumber((string) $payload['phc'], (string) $payload['phn']);
        }

        return $retry;
    }

    private static function createPhoneNumber(int $countryCode = 33, string $number = null)
    {
        $phone = new PhoneNumber();
        $phone->setCountryCode($countryCode);

        if ($number) {
            $phone->setNationalNumber($number);
        }

        return $phone;
    }
}
