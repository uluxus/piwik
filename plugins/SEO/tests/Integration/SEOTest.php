<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SEO\tests\Integration;

use Piwik\DataTable\Renderer;
use Piwik\Plugins\SEO\API;
use Exception;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group SEO
 * @group SEOTest
 * @group Plugins
 */
class SEOTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // setup the access layer
        FakeAccess::setIdSitesView(array(1, 2));
        FakeAccess::setIdSitesAdmin(array(3, 4));

        //finally we set the user as a Super User by default
        FakeAccess::$superUser = true;

        $user_agents = array(
            'Mozilla/5.0 (X11; Fedora; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.0 Safari/605.1.15',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36',
        );

        $_SERVER['HTTP_USER_AGENT'] = $user_agents[mt_rand(0, count($user_agents) - 1)];
    }

    /**
     * tell us when the API is broken
     */
    public function test_API()
    {
        try {
            $dataTable = API::getInstance()->getRank('http://www.microsoft.com/');
        } catch(Exception $e) {
            $this->markTestSkipped('A SEO http request failed, Skipping this test for now. Error was: '.$e->getMessage());
        }
        $renderer = Renderer::factory('json');
        $renderer->setTable($dataTable);
        $ranks = json_decode($renderer->render(), true);
        foreach ($ranks as $rank) {
            if ($rank["id"] == "alexa") { // alexa is broken at the moment
                continue;
            }
            $this->assertNotEmpty($rank['rank'], $rank['id'] . ' expected non-zero rank, got [' . $rank['rank'] . ']');
        }
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }
}
