@extends('layouts.admin')

@section('title', 'Создание группы')
@section('page-title', 'Создать группу')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-users me-2"></i>Создание группы
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.groups.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Название группы *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Описание</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="4">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="course_id" class="form-label">Курс</label>
                                    <select class="form-select @error('course_id') is-invalid @enderror"
                                            id="course_id" name="course_id">
                                        <option value="">Не привязан к курсу</option>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}" @selected(old('course_id') == $course->id)>
                                                {{ $course->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('course_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="program_id" class="form-label">Программа</label>
                                    <select class="form-select @error('program_id') is-invalid @enderror"
                                            id="program_id" name="program_id">
                                        <option value="">Не привязана к программе</option>
                                        @foreach($programs as $program)
                                            <option value="{{ $program->id }}" @selected(old('program_id') == $program->id)>
                                                {{ $program->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('program_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                       value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Активная группа
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.groups.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Назад
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Создать группу
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
