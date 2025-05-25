@extends('admin.layouts.app')

@push('libraries_top')
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item">{{ $pageTitle }}</div>
            </div>
        </div>

        <div class="section-body">
            <section class="card">
                <div class="card-body">
                    <form action="{{ getAdminPanelUrl() }}/certificates/course-competition" method="get" class="row mb-0">

                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label class="input-label d-block">{{ trans('admin/main.class') }}</label>
                                <select name="webinars_ids[]" multiple="multiple"
                                    class="form-control search-webinar-select2"
                                    data-placeholder="{{ trans('admin/main.search_webinar') }}">
                                    @if (!empty($webinars))
                                        @foreach ($webinars as $webinar)
                                            <option value="{{ $webinar->id }}" selected="selected">
                                                {{ $webinar ? $webinar->title : '' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label class="input-label">البرنامج</label>
                                <select name="bundle_ids[]" multiple="multiple" class="form-control search-bundle-select2"
                                    data-placeholder="البرنامج">
                                    @if (!empty($bundles))
                                        @foreach ($bundles as $bundle)
                                            <option value="{{ $bundle->id }}" selected>{{ $bundle->title }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="input-label">{{ trans('admin/main.instructor') }}</label>
                                <select name="teacher_ids[]" multiple="multiple" data-search-option="just_teacher_role"
                                    class="form-control search-user-select2" data-placeholder="Search teachers">
                                    @if (!empty($teachers) and $teachers->count() > 0)
                                        @foreach ($teachers as $teacher)
                                            <option value="{{ $teacher->id }}" selected>{{ $teacher->full_name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label class="input-label">كود الطالب</label>
                                <input type="text" name="student_code" class="form-control" placeholder="كود الطالب"
                                    value="{{ request()->get('student_code') }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label class="input-label">البريد الإلكتروني</label>
                                <input type="text" name="email" class="form-control" placeholder="البريد الإلكتروني"
                                    value="{{ request()->get('email') }}">
                            </div>
                        </div>

                        {{-- <div class="col-md-3">
                            <div class="form-group">
                                <label class="input-label">{{ trans('admin/main.student') }}</label>
                                <select name="student_ids[]" multiple="multiple" data-search-option="just_student_role" class="form-control search-user-select2"
                                        data-placeholder="Search students">
                                    @if (!empty($students) and $students->count() > 0)
                                        @foreach ($students as $student)
                                            <option value="{{ $student->id }}" selected>{{ $student->full_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div> --}}

                        <div class="col-12 col-md-3 d-flex align-items-center justify-content-end">
                            <button type="submit"
                                class="btn btn-primary w-100">{{ trans('public.show_results') }}</button>
                        </div>
                    </form>
                </div>
            </section>

            <div class="row">
                <div class="col-12 col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <!-- Separate form for bulk delete -->
                            <form id="bulkDeleteForm" action="{{ route('admin.certificates.deleteSelected') }}"
                                method="POST">
                                @csrf
                                @method('DELETE')
                                <div class="table-responsive">
                                    <!-- Button to delete selected certificates -->
                                    <div class="card-footer">
                                        <button type="submit" class="btn btn-danger">حذف من المجلد</button>
                                    </div>
                                    <table class="table table-striped font-14">
                                        <thead>
                                            <tr>
                                                <th>select</th>
                                                <th>#</th>
                                                <th class="text-left">{{ trans('admin/main.title') }}</th>
                                                <th class="text-left">كود الشهادة</th>
                                                <th class="text-left">{{ trans('quiz.student') }}</th>
                                                <th class="text-left">كود الطالب</th>
                                                <th class="text-left">البريد الإلكتروني</th>
                                                <th class="text-left">{{ trans('admin/main.instructor') }}</th>
                                                <th class="text-center">{{ trans('public.date_time') }}</th>
                                                <th>{{ trans('admin/main.action') }}</th>
                                                <th>تعديل تاريخ الشهاده</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($certificates as $certificate)
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" form="bulkDeleteForm"
                                                            name="certificate_ids[]" value="{{ $certificate->id }}" />
                                                    </td>
                                                    <td class="text-center">{{ $certificate->id }}</td>
                                                    <td class="text-left">
                                                        @if ($certificate->webinar_id)
                                                            <span>{{ $certificate->webinar->title }}</span>
                                                        @elseif ($certificate->bundle_id)
                                                            <span>{{ $certificate->bundle->title ?? 'N/A' }}</span>
                                                        @else
                                                            <span>N/A</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-left">
                                                        <span>{{ $certificate->certificate_code }}</span>
                                                    </td>
                                                    <td class="text-left">{{ $certificate->student->full_name }}</td>
                                                    <td class="text-left">{{ $certificate->student->user_code ?? 'N/A' }}
                                                    </td>
                                                    <td class="text-left">{{ $certificate->student->email ?? 'N/A' }}</td>
                                                    <td class="text-left">
                                                        @if ($certificate->webinar_id)
                                                            <span>{{ $certificate->webinar->teacher->full_name ?? 'N/A' }}</span>
                                                        @elseif ($certificate->bundle_id)
                                                            <span>{{ $certificate->bundle->teacher->full_name ?? 'N/A' }}</span>
                                                        @else
                                                            <span>N/A</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        {{ dateTimeFormat($certificate->created_at, 'j M Y') }}</td>
                                                    <td>
                                                        <a href="{{ getAdminPanelUrl() }}/certificates/{{ $certificate->id }}/download"
                                                            target="_blank" class="btn-transparent text-primary"
                                                            data-toggle="tooltip"
                                                            title="{{ trans('quiz.download_certificate') }}">
                                                            <i class="fa fa-download" aria-hidden="true"></i>
                                                        </a>
                                                    </td>
                            </form>
                            <td class="align-middle">
                                <form
                                    action="{{ route('admin.certificate.update_graduation_date', ['certificate' => $certificate->id]) }}"
                                    method="POST" class="d-flex align-items-center">
                                    @csrf
                                    @method('PUT')

                                    <input type="text" name="graduation_date"
                                        class="form-control form-control-sm datepicker @error('graduation_date') is-invalid @enderror "
                                        value="{{ old('graduation_date', $certificate->graduation_date ? \Illuminate\Support\Carbon::parse($certificate->graduation_date)->format('Y-m-d') : '') }}"
                                        placeholder="{{ trans('admin/main.select_date') }}"
                                        style="width: 100px; height: 38px; padding: 6px 12px; font-size: 14px; line-height: 1.5; border-radius: 4px; border: 1px solid #ddd;" />

                                    <button type="submit"
                                        class="btn btn-sm btn-primary m-2">{{ trans('public.save') }}</button>

                                    @error('graduation_date')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </form>
                            </td>
                            </tr>
                            @endforeach
                            </tbody>
                            </table>
                        </div>


                    </div>
                </div>
            </div>
        </div>
        </div>
    </section>
@endsection
