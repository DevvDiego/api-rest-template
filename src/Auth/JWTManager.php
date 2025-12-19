<?php

namespace App\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use DomainException;
use UnexpectedValueException;

class JWTManager{

    private string $secret;
    private string $algorithm = 'HS256';
    private int $expiryHours = 24;
    
    public function __construct(string $secret){
        
        if (strlen($secret) < 32) {
            throw new \InvalidArgumentException("JWT secret too short");
        
        }

        $this->secret = $secret;
    }
    

    /**
     * Crea un token JWT para el usuario
    */
    public function createToken(string $userId, array $additionalData = []): string{
        $issuedAt = time();
        $expire = $issuedAt + ($this->expiryHours * 3600);
        
        $payload = array_merge([
            'iss' => $_SERVER['HTTP_HOST'] ?? 'localhost',  // Emisor (tu dominio)
            'iat' => $issuedAt,         // Fecha de emision
            'exp' => $expire,           // Fecha de expiracion
            'sub' => $userId,           // Sujeto (ID usuario)
            'role' => 'admin',          // Rol
            'jti' => bin2hex(random_bytes(16)) // ID unico del token

        ], $additionalData);
        
        return JWT::encode($payload, $this->secret, $this->algorithm);
    }
    

    /**
     * Valida y decodifica un token JWT
    */
    public function validateToken(string $token): ?array{
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));
            return (array) $decoded;

        } catch (ExpiredException $e) {
            // Token expirado
            error_log('Token expirado: ' . $e->getMessage() . " Timestamp: " . time());
            return null;

        } catch (SignatureInvalidException $e) {
            // Firma invalida - posible intento de manipulación
            error_log('Firma JWT inválida: ' . $e->getMessage() . " Timestamp: " . time());
            return null;

        } catch (DomainException | UnexpectedValueException $e) {
            // Token mal formado
            error_log('Token JWT mal formado: ' . $e->getMessage() . " Timestamp: " . time());
            return null;

        } catch (\Exception $e) {
            // Cualquier otro error
            error_log('Error validando JWT: ' . $e->getMessage() . " Timestamp: " . time());
            return null;

        }
    }
    

    public function refreshToken(string $oldToken): ?string{
        $payload = $this->validateToken($oldToken);
        
        if (!$payload || !isset($payload['sub'])) {
            return null;
        }
        
        // Crear nuevo token con misma información
        return $this->createToken($payload['sub'], [
            'role' => $payload['role'] ?? 'admin'
        ]);
    }
}