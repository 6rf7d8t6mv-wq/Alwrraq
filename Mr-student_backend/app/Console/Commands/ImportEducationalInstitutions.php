<?php

namespace App\Console\Commands;

use App\Models\EducationalInstitution;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ImportEducationalInstitutions extends Command
{
    protected $signature = 'institutions:import
        {file : Official CSV file path}
        {--source= : Official source name}
        {--source-url= : Official source URL}
        {--type= : Default institution type}
        {--stage= : Default education stage}
        {--ownership= : Default ownership type}
        {--gender=mixed : Default gender type}
        {--inactive : Mark imported records as inactive}
        {--dry-run : Validate the CSV without saving}';

    protected $description = 'Import official Saudi educational institutions from an official CSV file.';

    private const TYPES = ['school', 'university', 'college', 'institute'];
    private const STAGES = ['kindergarten', 'primary', 'intermediate', 'secondary', 'higher_education', 'training'];
    private const OWNERSHIPS = ['government', 'private', 'international'];
    private const GENDERS = ['boys', 'girls', 'mixed'];

    public function handle(): int
    {
        $path = $this->resolvePath($this->argument('file'));

        if (! $path || ! is_readable($path)) {
            $this->error('CSV file was not found or is not readable.');

            return self::FAILURE;
        }

        $source = trim((string) $this->option('source'));
        $sourceUrl = trim((string) $this->option('source-url'));

        if ($source === '') {
            $this->error('Use --source with the official source name.');

            return self::FAILURE;
        }

        $handle = fopen($path, 'r');

        if ($handle === false) {
            $this->error('Unable to open CSV file.');

            return self::FAILURE;
        }

        $headers = $this->headers(fgetcsv($handle) ?: []);

        if (! in_array('name_ar', $headers, true)) {
            $this->error('CSV must include a name_ar column.');
            fclose($handle);

            return self::FAILURE;
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $line = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $line++;
            $record = $this->record($headers, $row, $source, $sourceUrl);
            $validator = Validator::make($record, $this->rules());

            if ($validator->fails()) {
                $skipped++;
                $this->warn("Skipped line {$line}: ".$validator->errors()->first());
                continue;
            }

            if ($this->option('dry-run')) {
                continue;
            }

            $key = $this->lookupKey($record);
            $institution = EducationalInstitution::query()->firstOrNew($key);
            $institution->fill($record);
            $institution->exists ? $updated++ : $created++;
            $institution->save();
        }

        fclose($handle);

        $this->info('Import completed.');
        $this->line("Created: {$created}");
        $this->line("Updated: {$updated}");
        $this->line("Skipped: {$skipped}");

        return self::SUCCESS;
    }

    private function resolvePath(string $file): ?string
    {
        $paths = [
            $file,
            base_path($file),
            storage_path($file),
        ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    private function headers(array $headers): array
    {
        return array_map(function ($header) {
            $header = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header);

            return strtolower(trim($header));
        }, $headers);
    }

    private function record(array $headers, array $row, string $source, string $sourceUrl): array
    {
        $data = [];

        foreach ($headers as $index => $header) {
            $data[$header] = isset($row[$index]) ? trim((string) $row[$index]) : null;
        }

        $ministerialNumber = $data['ministerial_number'] ?? null;
        $officialId = $data['official_id'] ?? $ministerialNumber;

        return [
            'official_id' => $this->blankToNull($officialId),
            'ministerial_number' => $this->blankToNull($ministerialNumber),
            'name_ar' => trim((string) ($data['name_ar'] ?? '')),
            'name_en' => $this->blankToNull($data['name_en'] ?? null),
            'institution_type' => $this->blankToNull($data['institution_type'] ?? null) ?: $this->option('type'),
            'education_stage' => $this->blankToNull($data['education_stage'] ?? null) ?: $this->option('stage'),
            'ownership_type' => $this->blankToNull($data['ownership_type'] ?? null) ?: $this->option('ownership'),
            'gender_type' => $this->blankToNull($data['gender_type'] ?? null) ?: $this->option('gender'),
            'region' => $this->blankToNull($data['region'] ?? null),
            'city' => $this->blankToNull($data['city'] ?? null),
            'district' => $this->blankToNull($data['district'] ?? null),
            'latitude' => $this->blankToNull($data['latitude'] ?? null),
            'longitude' => $this->blankToNull($data['longitude'] ?? null),
            'source' => $this->blankToNull($data['source'] ?? null) ?: $source,
            'source_url' => $this->blankToNull($data['source_url'] ?? null) ?: $sourceUrl,
            'is_active' => ! $this->option('inactive') && $this->booleanValue($data['is_active'] ?? true),
            'last_verified_at' => $this->blankToNull($data['last_verified_at'] ?? null) ?: Carbon::now(),
        ];
    }

    private function rules(): array
    {
        return [
            'official_id' => ['nullable', 'string', 'max:255'],
            'ministerial_number' => ['nullable', 'string', 'max:255'],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'institution_type' => ['required', Rule::in(self::TYPES)],
            'education_stage' => ['required', Rule::in(self::STAGES)],
            'ownership_type' => ['required', Rule::in(self::OWNERSHIPS)],
            'gender_type' => ['required', Rule::in(self::GENDERS)],
            'region' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'source' => ['required', 'string', 'max:255'],
            'source_url' => ['nullable', 'url', 'max:2048'],
            'is_active' => ['boolean'],
            'last_verified_at' => ['nullable', 'date'],
        ];
    }

    private function lookupKey(array $record): array
    {
        if (! empty($record['official_id'])) {
            return [
                'source' => $record['source'],
                'official_id' => $record['official_id'],
            ];
        }

        return [
            'name_ar' => $record['name_ar'],
            'city' => $record['city'],
            'institution_type' => $record['institution_type'],
        ];
    }

    private function blankToNull(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function booleanValue(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'active', 'نشط'], true);
    }
}
