<?php

namespace App\Imports;

use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Course;
use App\Models\CoursesTrainee;
use App\Models\Trainee;
use Exception;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ParticipantsImport implements ToCollection, WithStartRow
{
    protected Collection $inserted;

    public $data;

    public function __construct($data = null)
    {
        $this->data = $data;
    }

    /**
     * @param \Illuminate\Support\Collection $collection
     * @throws \Exception
     */
    public function collection(Collection $collection)
    {
        $collection->pipe(function ($rows) {
            $this->validateColumns($rows);

            return $rows;
        })->pipe(function ($rows) {
            return $this->skipEmptyLines($rows);
        })->pipe(function ($rows) {
            return $this->combineWithHeadings($rows);
        })->pipe(function ($rows) {
            $this->checkDuplicatedEmails($rows);

            return $rows;
        })->tap(function ($rows) {
            $this->inserted = $this->insertRows($rows);
        });
    }

    protected function combineWithHeadings(Collection $rows): Collection
    {
        $headings = collect($this->headings());

        return $rows->map(function ($row) use ($headings) {
            return $headings->mapWithKeys(function ($heading, $index) use ($row) {
                return [$heading => $row->values()->get($index)];
            });
        });
    }

    protected function skipEmptyLines(Collection $rows): Collection
    {
        return $rows->reject(function ($row) {
            return empty($row[0]); // empty name
        });
    }

    /**
     * @throws \Exception
     */
    public function insertRows(Collection $rows): Collection
    {
        $data = collect();
        $rows->each(function ($row) use (&$data) {
            $rec = Trainee::query()->firstOrCreate(['name' => $row->get('name')], $row->toArray());
            if ($comp = Company::query()->where('name', $row->get('company'))->first()) {
                CompanyUser::query()->firstOrCreate([
                    'company_id' => $comp->id,
                    'user_id' => $rec->id,
                ]);
            }
            $course = Course::find($this->data['course_id']);

            CoursesTrainee::firstOrCreate(['user_id' => $rec->id, 'course_id' => $course->id, 'price' => 0]);
            if (request('company', -1) > 0) {
                CompanyUser::query()->firstOrCreate([
                    'company_id' => $this->data['company_id'],
                    'user_id' => $rec->id,
                ]);
            }

            $data->push($rec);
        });


        // dd($data);
        return $data;

    }

    public function validateColumns(Collection $rows)
    {
        $headings = $this->headings();
        $columns = $rows->first()->keys();

        echo count($headings);
        echo count($columns);

        // if (count($headings) != count($columns)) {
        //     abort(Response::HTTP_UNPROCESSABLE_ENTITY, "Invalid template" . count($headings) . '  ' . count($columns));
        // }

    }

    /**
     * @throws \Exception
     */
    public function checkDuplicatedEmails(Collection $collection)
    {
        $email = '';
        /** @var Collection $counts */
        $counts = $collection->groupBy('email')->map->count();
        $duplicates = $counts->filter(function ($value, $key) use (&$email) {
            if (!empty($key) && $value >= 2) {
                $email = $key;

                return true;
            }


            return false;
        })->isNotEmpty();


    }

    /**
     * Get the headings.
     *
     * @return string[]
     */
    protected function headings(): array
    {
        return [
            'name',

            'phone',
            'notes',
            'company',
            'company_name',
            'title',
        ];
    }

    public function startRow(): int
    {
        return 2;
    }

    public function getCount(): int
    {
        return $this->inserted->count();
    }

    public function getInsertedData(): Collection
    {
        return $this->inserted;
    }
}
