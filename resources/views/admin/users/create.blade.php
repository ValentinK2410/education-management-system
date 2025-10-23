@extends('layouts.admin')

@section('title', 'Создание пользователя')
@section('page-title', 'Создать пользователя')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-user-plus me-2"></i>Создание пользователя
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Имя *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           id="email" name="email" value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Пароль *</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                           id="password" name="password" required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Телефон</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                           id="phone" name="phone" value="{{ old('phone') }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Фото профиля -->
                        <div class="mb-3">
                            <label for="photo" class="form-label">Фото профиля</label>
                            <input type="file" class="form-control @error('photo') is-invalid @enderror" 
                                   id="photo" name="photo" accept="image/*">
                            @error('photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Поддерживаемые форматы: JPG, PNG, GIF. Максимальный размер: 2MB</div>
                        </div>

                        <!-- Личная информация -->
                        <h5 class="mb-3">Личная информация</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="city" class="form-label">Город</label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                           id="city" name="city" value="{{ old('city') }}">
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="religion" class="form-label">Вероисповедание</label>
                                    <select class="form-select @error('religion') is-invalid @enderror" 
                                            id="religion" name="religion">
                                        <option value="">Выберите вероисповедание</option>
                                        <option value="Православие" {{ old('religion') == 'Православие' ? 'selected' : '' }}>Православие</option>
                                        <option value="Католицизм" {{ old('religion') == 'Католицизм' ? 'selected' : '' }}>Католицизм</option>
                                        <option value="Протестантизм" {{ old('religion') == 'Протестантизм' ? 'selected' : '' }}>Протестантизм</option>
                                        <option value="Ислам" {{ old('religion') == 'Ислам' ? 'selected' : '' }}>Ислам</option>
                                        <option value="Иудаизм" {{ old('religion') == 'Иудаизм' ? 'selected' : '' }}>Иудаизм</option>
                                        <option value="Буддизм" {{ old('religion') == 'Буддизм' ? 'selected' : '' }}>Буддизм</option>
                                        <option value="Другое" {{ old('religion') == 'Другое' ? 'selected' : '' }}>Другое</option>
                                        <option value="Не указано" {{ old('religion') == 'Не указано' ? 'selected' : '' }}>Не указано</option>
                                    </select>
                                    @error('religion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="church" class="form-label">Название церкви</label>
                                    <input type="text" class="form-control @error('church') is-invalid @enderror" 
                                           id="church" name="church" value="{{ old('church') }}">
                                    @error('church')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="marital_status" class="form-label">Семейное положение</label>
                                    <select class="form-select @error('marital_status') is-invalid @enderror" 
                                            id="marital_status" name="marital_status">
                                        <option value="">Выберите семейное положение</option>
                                        <option value="single" {{ old('marital_status') == 'single' ? 'selected' : '' }}>Холост/Не замужем</option>
                                        <option value="married" {{ old('marital_status') == 'married' ? 'selected' : '' }}>Женат/Замужем</option>
                                        <option value="divorced" {{ old('marital_status') == 'divorced' ? 'selected' : '' }}>Разведен/Разведена</option>
                                        <option value="widowed" {{ old('marital_status') == 'widowed' ? 'selected' : '' }}>Вдовец/Вдова</option>
                                    </select>
                                    @error('marital_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="education" class="form-label">Образование</label>
                                    <select class="form-select @error('education') is-invalid @enderror" 
                                            id="education" name="education">
                                        <option value="">Выберите образование</option>
                                        <option value="Среднее" {{ old('education') == 'Среднее' ? 'selected' : '' }}>Среднее</option>
                                        <option value="Среднее специальное" {{ old('education') == 'Среднее специальное' ? 'selected' : '' }}>Среднее специальное</option>
                                        <option value="Неполное высшее" {{ old('education') == 'Неполное высшее' ? 'selected' : '' }}>Неполное высшее</option>
                                        <option value="Высшее" {{ old('education') == 'Высшее' ? 'selected' : '' }}>Высшее</option>
                                        <option value="Магистратура" {{ old('education') == 'Магистратура' ? 'selected' : '' }}>Магистратура</option>
                                        <option value="Аспирантура" {{ old('education') == 'Аспирантура' ? 'selected' : '' }}>Аспирантура</option>
                                        <option value="Докторантура" {{ old('education') == 'Докторантура' ? 'selected' : '' }}>Докторантура</option>
                                    </select>
                                    @error('education')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                               value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Активный пользователь
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- О себе -->
                        <div class="mb-3">
                            <label for="about_me" class="form-label">О себе</label>
                            <textarea class="form-control @error('about_me') is-invalid @enderror" 
                                      id="about_me" name="about_me" rows="3" 
                                      placeholder="Расскажите немного о себе...">{{ old('about_me') }}</textarea>
                            @error('about_me')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="bio" class="form-label">Биография</label>
                            <textarea class="form-control @error('bio') is-invalid @enderror"
                                      id="bio" name="bio" rows="3" 
                                      placeholder="Подробная биография...">{{ old('bio') }}</textarea>
                            @error('bio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Роли *</label>
                            <div class="row">
                                @foreach($roles as $role)
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                   name="roles[]" value="{{ $role->id }}"
                                                   id="role_{{ $role->id }}"
                                                   {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="role_{{ $role->id }}">
                                                {{ $role->name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('roles')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Назад
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Создать пользователя
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
