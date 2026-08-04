<?php

namespace Tests\Unit;

use App\Services\AiTools\ToolExecutor;
use App\Services\AiTools\ToolRegistry;
use Tests\TestCase;

class AiToolCallingTest extends TestCase
{
    public function test_tool_registry_returns_valid_tools(): void
    {
        $tools = ToolRegistry::getOpenAiTools();

        $this->assertIsArray($tools);
        $this->assertNotEmpty($tools);

        // Verify each tool has the required structure
        foreach ($tools as $tool) {
            $this->assertArrayHasKey('type', $tool);
            $this->assertEquals('function', $tool['type']);
            $this->assertArrayHasKey('function', $tool);
            $this->assertArrayHasKey('name', $tool['function']);
            $this->assertArrayHasKey('description', $tool['function']);
            $this->assertArrayHasKey('parameters', $tool['function']);
        }
    }

    public function test_gemini_tools_format(): void
    {
        $tools = ToolRegistry::getGeminiTools();

        $this->assertIsArray($tools);
        $this->assertNotEmpty($tools);
        $this->assertArrayHasKey('functionDeclarations', $tools[0]);
    }

    public function test_tool_names_are_unique(): void
    {
        $tools = ToolRegistry::getTools();
        $names = array_column($tools, 'name');

        $this->assertEquals(count($names), count(array_unique($names)));
    }

    public function test_tool_executor_handles_unknown_tool(): void
    {
        $executor = new ToolExecutor('test_sender');
        $result = $executor->execute('unknown_tool', []);

        $this->assertArrayHasKey('error', $result);
    }

    public function test_search_products_requires_query(): void
    {
        $executor = new ToolExecutor('test_sender');
        $result = $executor->execute('search_products', []);

        $this->assertArrayHasKey('error', $result);
    }

    public function test_get_product_details_requires_id(): void
    {
        $executor = new ToolExecutor('test_sender');
        $result = $executor->execute('get_product_details', []);

        $this->assertArrayHasKey('error', $result);
    }

    public function test_get_delivery_charge_requires_area(): void
    {
        $executor = new ToolExecutor('test_sender');
        $result = $executor->execute('get_delivery_charge', []);

        $this->assertArrayHasKey('error', $result);
    }
}
