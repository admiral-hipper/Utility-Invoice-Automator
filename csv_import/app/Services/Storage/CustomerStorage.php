<?php

namespace App\Services\Storage;

use App\Models\Customer;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class CustomerStorage
{
    private Filesystem $disk;

    public function __construct()
    {
        $this->disk = Storage::disk('invoices');
    }

    /**
     * Put file to User invoice disk
     *
     * @param  string  $path
     * @param  \Psr\Http\Message\StreamInterface|\Illuminate\Http\File|\Illuminate\Http\UploadedFile|string|resource  $path
     * @param  mixed  $options
     */
    public function putToUserDir(Customer $customer, $path, $contents, $options = []): bool
    {
        return $this->disk->put("{$customer->id}/".$path, $contents, $options);
    }

    public function download(Customer $customer, string $path, ?string $downloadName = null): StreamedResponse
    {
        $relative = $this->customerPath($customer, $path);

        abort_unless($this->disk->exists($relative), 404);

        $downloadName ??= basename($path);

        // Let filesystem stream it (works for local, s3, etc.)
        return $this->disk->download($relative, $downloadName, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function inline(Customer $customer, string $path, ?string $displayName = null): StreamedResponse
    {
        dd($path);
        $relative = $this->customerPath($customer, $path);

        abort_unless($this->disk->exists($relative), 404);

        $displayName ??= basename($path);

        // Stream file to browser as inline
        return response()->streamDownload(function () use ($relative) {
            $stream = $this->disk->readStream($relative);

            if ($stream === false) {
                abort(404);
            }

            fpassthru($stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        }, $displayName, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$displayName.'"',
        ]);
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

    public function customerPath(Customer $customer, string $path): string
    {
        return $customer->id.'/'.ltrim($path, '/');
    }
}
