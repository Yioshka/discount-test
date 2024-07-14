<?php

declare(strict_types=1);

namespace App\Tests;

use App\Controller\Api\V1\Price\Action;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

#[CoverClass(Action::class)]
final class PriceTest extends WebTestCase
{
    private static RouterInterface $router;

    private static KernelBrowser $client;

    /**
     * @throws \Exception
     */
    public static function setUpBeforeClass(): void
    {
        $client = static::createClient();
        $router = $client->getContainer()->get('router');

        if (! $router instanceof RouterInterface) {
            throw new \Exception('Router not found');
        }

        self::$client = $client;
        self::$router = $router;
    }

    public function testGetPriceWithoutDiscount(): void
    {
        $price = 100;

        self::$client->request(
            method: 'POST',
            uri: self::$router->generate('api_v1_price_calculate'),
            parameters: [
                'birthDate' => '01.01.2002',
                'startDate' => '01.01.2027',
                'paymentDate' => '01.01.2028',
                'price' => $price,
            ]
        );

        $result = self::getResponseContent(self::$client->getResponse());

        self::assertTrue($result->success);
        /**
         * @psalm-suppress MixedPropertyFetch
         */
        self::assertSame($price, $result->data->price);
    }

    public function testGetPriceWithChildDiscount(): void
    {
        self::$client->request(
            method: 'POST',
            uri: self::$router->generate('api_v1_price_calculate'),
            parameters: [
                'birthDate' => '01.01.2012',
                'startDate' => '01.01.2027',
                'paymentDate' => '01.01.2028',
                'price' => 100,
            ]
        );

        $result = self::getResponseContent(self::$client->getResponse());

        self::assertTrue($result->success);
        /**
         * @psalm-suppress MixedPropertyFetch
         */
        self::assertSame(90, $result->data->price);
    }

    public function testGetPriceWithChildDiscountMax(): void
    {
        self::$client->request(
            method: 'POST',
            uri: self::$router->generate('api_v1_price_calculate'),
            parameters: [
                'birthDate' => '01.01.2020',
                'startDate' => '01.01.2027',
                'paymentDate' => '01.01.2028',
                'price' => 100000,
            ]
        );

        $result = self::getResponseContent(self::$client->getResponse());

        self::assertTrue($result->success);
        /**
         * @psalm-suppress MixedPropertyFetch
         */
        self::assertSame(95500, $result->data->price);
    }

    public function testGetPriceWithEarlyDiscount(): void
    {
        self::$client->request(
            method: 'POST',
            uri: self::$router->generate('api_v1_price_calculate'),
            parameters: [
                'birthDate' => '01.05.2002',
                'startDate' => '01.05.2027',
                'paymentDate' => '30.11.2026',
                'price' => 100,
            ]
        );

        $result = self::getResponseContent(self::$client->getResponse());

        self::assertTrue($result->success);
        /**
         * @psalm-suppress MixedPropertyFetch
         */
        self::assertSame(93, $result->data->price);
    }

    public function testGetPriceWithEarlyDiscountMax(): void
    {
        self::$client->request(
            method: 'POST',
            uri: self::$router->generate('api_v1_price_calculate'),
            parameters: [
                'birthDate' => '01.05.2002',
                'startDate' => '05.01.2027',
                'paymentDate' => '30.03.2024',
                'price' => 100000,
            ]
        );

        $result = self::getResponseContent(self::$client->getResponse());

        self::assertTrue($result->success);
        /**
         * @psalm-suppress MixedPropertyFetch
         */
        self::assertSame(98500, $result->data->price);
    }

    public function testGetPriceWithEarlyAndChildDiscount(): void
    {
        self::$client->request(
            method: 'POST',
            uri: self::$router->generate('api_v1_price_calculate'),
            parameters: [
                'birthDate' => '01.05.2020',
                'startDate' => '01.05.2027',
                'paymentDate' => '30.11.2026',
                'price' => 1000,
            ]
        );

        $result = self::getResponseContent(self::$client->getResponse());

        self::assertTrue($result->success);
        /**
         * @psalm-suppress MixedPropertyFetch
         */
        self::assertSame(186, $result->data->price);
    }

    public function testGetPriceWithInvalidData(): void
    {
        self::$client->request(
            method: 'POST',
            uri: self::$router->generate('api_v1_price_calculate'),
            parameters: [
                'birthDate' => '',
                'startDate' => '01.05.2027',
                'paymentDate' => '30.11.2026',
                'price' => 1000,
            ]
        );

        $result = self::getResponseContent(self::$client->getResponse());
        $status = self::$client->getResponse()->getStatusCode();

        self::assertSame(422, $status);
        self::assertFalse($result->success);
    }

    private static function getResponseContent(Response $response): object
    {
        $content = $response->getContent();
        self::assertNotFalse($content);

        $content = json_decode($content);
        self::assertTrue(is_object($content));

        return $content;
    }
}
