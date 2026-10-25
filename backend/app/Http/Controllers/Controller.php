<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "API de E-commerce Segura con Swagger Completo",
    description: "API RESTful para la gestion de clientes, catalogo de productos, ordenes de compra y procesamiento de pagos mediante Stripe. Construida con Laravel 12 y autenticacion basada en tokens (Sanctum)."
)]
#[OA\Server(url: L5_SWAGGER_CONST_HOST, description: "Servidor de la API")]
#[OA\SecurityScheme(
    securityScheme: "sanctum",
    type: "http",
    scheme: "bearer",
    bearerFormat: "Token",
    description: "Introduzca el token con el formato: Bearer {token}"
)]
#[OA\Tag(name: "Autenticacion", description: "Registro, login y logout de clientes")]
#[OA\Tag(name: "Productos", description: "Catalogo de productos")]
#[OA\Tag(name: "Ordenes", description: "Creacion y consulta de ordenes de compra")]
#[OA\Tag(name: "Pagos", description: "Procesamiento de pagos mediante Stripe")]
abstract class Controller
{
    //
}