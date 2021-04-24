<?php

/** @var \Laravel\Lumen\Routing\Router $router */

$router->get('/status', function (\Illuminate\Http\Request $request) use ($router) {
    return response("Healthy", 200);
});

$router->post('/api/v1/execute', function (\Illuminate\Http\Request $request) use ($router) {
    if (!$request->isJson()) {
        return response("Invalid JSON body", 400);
    }
    $cpu = $request->json();
    $opcode = $cpu->get('opcode');
    $state = $cpu->get('state');
    $hl = ($state['h'] << 8) | $state['l'];
    switch ($opcode) {
        case "9": // DAD BC
            $operand = ($state['b'] << 8) | $state['c'];
            break;
        case "25": // DAD DE
            $operand = ($state['d'] << 8) | $state['e'];
            break;
        case "41": // DAD HL
            $operand = $hl;
            break;
        case "57": // DAD SP
            $operand = $state['stackPointer'] << 8;
            break;
        default:
            return response("Invalid opcode", 400);
    };
    $result = ($hl + $operand);
    $h = ($result & 0xFF00) >> 8;
    $l = $result & 0xFF;

    return response()->json([
        'opcode' => $opcode,
        'id' => $cpu->get('id'),
        'state' => [
            'a' => $state['a'],
            'b' => $state['b'],
            'c' => $state['c'],
            'd' => $state['d'],
            'e' => $state['e'],
            'h' => $h,
            'l' => $l,
            'stackPointer' => $state['stackPointer'],
            'programCounter' => $state['programCounter'],
            'cycles' => $state['cycles'] + 10,
            'flags' => [
                'sign' => $state['flags']['sign'],
                'zero' => $state['flags']['zero'],
                'auxCarry' => $state['flags']['auxCarry'],
                'parity' => $state['flags']['parity'],
                'carry' => $result > 0xFFFF,
            ]
        ]
    ]);
});
