<?php

namespace App\Services\Storage;

use App\Models\Customer;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;


final class CustomerStorage
{
    private Filesystem $disk;

    public function __construct()
    {
        $this->disk = Storage::disk('invoices');
    }
    /**
     * Put file to User invoice disk
     * @param Customer $customer
     * @param string $path
     * @param \Psr\Http\Message\StreamInterface|\Illuminate\Http\File|\Illuminate\Http\UploadedFile|string|resource $path
     * @param mixed $options
     */
    public function putToUserDir($customer, $path, $contents, $options = []): bool
    {
        return $this->disk->put("{$customer->id}/" . $path, $contents, $options);
    }

    public function exists(Customer $customer, string $path): bool
    {
        return $this->disk->exists(
            $this->customerPath($customer, $path)
        );
    }

    public function delete(Customer $customer, string $path): bool
    {
        return $this->disk->delete(
            $this->customerPath($customer, $path)
        );
    }

    public function path(Customer $customer, string $path): string
    {
        return $this->disk->path(
            $this->customerPath($customer, $path)
        );
    }

    private function customerPath(Customer $customer, string $path): string
    {
        return $customer->id . '/' . ltrim($path, '/');
    }
}
