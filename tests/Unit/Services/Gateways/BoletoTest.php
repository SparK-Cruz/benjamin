<?php
namespace Tests\Unit\Services\Gateways;

use Ebanx\Benjamin\Models\Configs\Config;
use Tests\Helpers\Builders\BuilderFactory;
use Tests\TestCase;

class BoletoTest extends TestCase
{
    public function testBusinessPersonPayment()
    {
        $this->loadEnv();

        $config = new Config([
            'sandboxIntegrationKey' => getenv('SANDBOX_INTEGRATION_KEY'),
            'publicSandboxIntegrationKey' => getenv('SANDBOX_PRIVATE_INTEGRATION_KEY')
        ]);

        $payment = BuilderFactory::payment()->boleto()->businessPerson()->build();
//        $result = Benjamin($config)->gateways()->boleto()->create($payment);
        $result = 'hash de pagamento';

        $this->assertEquals('hash de pagamento', $result);

        // TODO: call main api and boleto gateway
        // TODO: assert output (to be defined)
    }
}
