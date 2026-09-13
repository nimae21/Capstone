<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use JsonException;
use RuntimeException;

class AddressDataController extends Controller
{
    public function barangays(Request $request, string $cityCode): JsonResponse
    {
        abort_unless(preg_match('/\A\d{6}\z/', $cityCode) === 1, 404);

        $range = config("address-data.barangays.{$cityCode}");
        abort_unless(is_array($range) && count($range) === 3, 404);

        $version = (string) config('address-data.version');
        $payload = Cache::rememberForever(
            "address-data:barangays:{$version}:{$cityCode}",
            fn (): array => $this->readBarangays($cityCode, $range),
        );

        $response = response()->json([
            'city_code' => $cityCode,
            'barangays' => $payload,
        ]);
        $response->setEtag(hash('sha256', $version.':'.$cityCode));
        $response->setPublic();
        $response->setMaxAge(31536000);
        $response->setSharedMaxAge(31536000);
        $response->headers->addCacheControlDirective('immutable');
        $response->isNotModified($request);

        return $response;
    }

    /** @param array{0: int, 1: int, 2: int} $range */
    private function readBarangays(string $cityCode, array $range): array
    {
        [$offset, $length, $expectedCount] = $range;
        $path = public_path((string) config('address-data.barangay_file'));
        $handle = @fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException('The address dataset is unavailable.');
        }

        try {
            if (fseek($handle, $offset) !== 0) {
                throw new RuntimeException('The address dataset index is invalid.');
            }

            $segment = stream_get_contents($handle, $length);
        } finally {
            fclose($handle);
        }

        if ($segment === false || strlen($segment) !== $length) {
            throw new RuntimeException('The address dataset could not be read completely.');
        }

        try {
            $records = json_decode('['.$segment.']', true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('The address dataset index is stale.', previous: $exception);
        }

        if (count($records) !== $expectedCount) {
            throw new RuntimeException('The address dataset index count is invalid.');
        }

        return array_map(function (array $record) use ($cityCode): array {
            if ((string) ($record['city_code'] ?? '') !== $cityCode || ! is_string($record['brgy_name'] ?? null)) {
                throw new RuntimeException('The address dataset contains an invalid record.');
            }

            return ['name' => $record['brgy_name']];
        }, $records);
    }
}
