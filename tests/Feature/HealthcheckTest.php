<?php

it('reports that the application is up', function (): void {
    $response = $this->getJson('/_healthcheck');

    $response->assertOk()->assertExactJson(['message' => 'ok']);
});
