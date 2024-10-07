<?php declare(strict_types=1);

namespace Tests\Functional\Api\Book;

use App\Actions\Api\Book\ManageAction;
use App\Exception\UnsupportedOperationException;
use App\Renderers\JsonErrorRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Functional\AbstractTestCase;
use Tests\PopulateBooksTrait;

#[CoversClass(ManageAction::class)]
#[CoversClass(UnsupportedOperationException::class)]
#[CoversClass(JsonErrorRenderer::class)]
class ManageActionTest extends AbstractTestCase
{
    use PopulateBooksTrait;

    public function testUnsupportedOperationThrowsException()
    {
        $request = $this->createJsonRequest('POST', '/api/book/manage');
        $response = $this->app->handle($request);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertJsonError("Operation '' is not supported!", 0, "App\\Exception\\UnsupportedOperationException", $response);
    }

}
