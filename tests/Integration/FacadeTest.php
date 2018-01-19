<?php
namespace Tests\Integration;

use Ebanx\Benjamin\Models\Payment;
use Tests\TestCase;
use Ebanx\Benjamin\Facade;
use Ebanx\Benjamin\Models\Configs\Config;
use Ebanx\Benjamin\Models\Configs\CreditCardConfig;
use Ebanx\Benjamin\Services\Http\Client as HttpClient;
use Ebanx\Benjamin\Services\Traits\Printable;
use Ebanx\Benjamin\Services\Gateways\BaseGateway;

class FacadeTest extends TestCase
{
    public function testMainObject()
    {
        $ebanx = EBANX(new Config(), new CreditCardConfig());
        $this->assertNotNull($ebanx);

        return $ebanx;
    }

    /**
     * @param Facade $ebanx
     * @depends testMainObject
     */
    public function testGatewayAccessors($ebanx)
    {
        $gateways = $this->getExpectedGateways();

        foreach ($gateways as $gateway) {
            $class = new \ReflectionClass('Ebanx\Benjamin\Services\Gateways\\' . ucfirst($gateway));

            // skip abstract gateways
            if ($class->isAbstract()) {
                continue;
            }

            $this->assertAccessor($ebanx, $gateway);
        }
    }

    /**
     * @param $ebanx
     * @depends testMainObject
     */
    public function testOtherServicesAccessors($ebanx)
    {
        $services = $this->getExpectedServices();

        foreach ($services as $service) {
            $class = new \ReflectionClass('Ebanx\Benjamin\Services\\' . ucfirst($service));

            // skip abstract gateways
            if ($class->isAbstract()) {
                continue;
            }

            $this->assertAccessor($ebanx, $service);
        }
    }

    /**
     * @param Facade $ebanx
     * @depends testMainObject
     * @expectedException \InvalidArgumentException
     */
    public function testCreatePaymentWithoutPaymentType($ebanx)
    {
        $ebanx->create(new Payment());
    }

    /**
     * @param Facade $ebanx
     * @depends testMainObject
     * @expectedException \InvalidArgumentException
     */
    public function testCreatePaymentWithWrongPaymentType($ebanx)
    {
        $ebanx->create(new Payment([
            'type' => 'invalidType',
        ]));
    }

    /**
     * @param Facade $ebanx
     * @depends testMainObject
     */
    public function testCreatePaymentByFacade($ebanx)
    {
        $ebanx = new FacadeForTests();
        $ebanx->addConfig(new Config());

        $result = $ebanx->create(new Payment([
            'type' => 'test',
        ]));

        $this->assertArrayHasKey('payment', $result);
    }

    public function testDefaultClientMode()
    {
        $ebanx = new FacadeForTests();
        $ebanx->addConfig(
            new Config([
                'isSandbox' => false
            ]),
            new CreditCardConfig()
        );

        $this->assertFalse(
            $ebanx->getHttpClient()->isSandbox(),
            'Client connection mode is ignoring config'
        );
    }

    public function testGetTicketHtml()
    {
        $hash = md5(rand());
        $expected = "<html>$hash</html>";
        $infoUrl = 'ws/query';
        $printUrl = "print/?hash=$hash";

        $ebanx = $this->buildMockedFacade([
            $infoUrl => $this->buildPaymentInfoMock($hash),
            $printUrl => $expected,
        ]);

        $subject = $ebanx->getTicketHtml($hash);

        $this->assertEquals($expected, $subject);
    }

    public function testGetTicketHtmlWithBadPaymentType()
    {
        $hash = md5(rand());
        $infoUrl = 'ws/query';
        $printUrl = "print/?hash=$hash";

        $ebanx = $this->buildMockedFacade([
            $infoUrl => $this->buildPaymentInfoMock($hash, 'none'),
            $printUrl => "<html>$hash</html>",
        ]);

        $subject = $ebanx->getTicketHtml($hash);

        $this->assertNull($subject);
    }

    public function testGetTicketHtmlWithNonPrintableGateway()
    {
        $hash = md5(rand());
        $infoUrl = 'ws/query';
        $printUrl = "print/?hash=$hash";

        $ebanx = $this->buildMockedFacade([
            $infoUrl => $this->buildPaymentInfoMock($hash, 'tef'),
            $printUrl => "<html>$hash</html>",
        ]);

        $subject = $ebanx->getTicketHtml($hash);

        $this->assertNull($subject);
    }

    private function getExpectedGateways()
    {
        $result = [];

        $dir = opendir('src/Services/Gateways');
        while (($file = readdir($dir)) !== false) {

            // skip non-php files
            if ($file === basename($file, '.php')) {
                continue;
            }

            $result[] = lcfirst(basename($file, '.php'));
        }
        closedir($dir);

        return $result;
    }

    private function getExpectedServices()
    {
        $result = [];

        $dir = opendir('src/Services');
        while (($file = readdir($dir)) !== false) {

            // skip non-php files
            if ($file === basename($file, '.php')) {
                continue;
            }

            $result[] = lcfirst(basename($file, '.php'));
        }
        closedir($dir);

        return $result;
    }

    private function buildMockedFacade($responseMock = null)
    {
        $ebanx = new FacadeForTests();
        $ebanx->addConfig(new Config());
        $ebanx->addConfig(new CreditCardConfig());

        if ($responseMock) {
            $ebanx->setHttpClient($this->getMockedClient($responseMock));
        }

        return $ebanx;
    }

    private function buildPaymentInfoMock($hash, $type = 'test')
    {
        return '{"payment":{"hash":"'.$hash.'","payment_type_code":"'.$type.'"},"status":"SUCCESS"}';
    }

    private function assertAccessor($facade, $name)
    {
        $this->assertTrue(
            method_exists($facade, $name),
            'Accessor method not defined'
        );

        $this->assertNotNull(
            $facade->{$name}(),
            'Accessor returned null service'
        );
    }
}

class GatewayForTests extends BaseGateway
{
    use Printable;

    const API_TYPE = 'test';

    public function create()
    {
        return ['payment' => []];
    }

    protected function getUrlFormat()
    {
        return 'https://%s.ebanx.com/print/?hash=%s';
    }
}

class FacadeForTests extends Facade
{
    public function test()
    {
        return new GatewayForTests($this->config, $this->getHttpClient());
    }

    public function getHttpClient()
    {
        return parent::getHttpClient();
    }

    public function setHttpClient(HttpClient $client)
    {
        $this->httpClient = $client;
    }
}
