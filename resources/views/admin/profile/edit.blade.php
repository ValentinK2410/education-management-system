@extends('layouts.admin')

@section('title', 'Редактировать профиль')
@section('page-title', 'Редактировать профиль')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-user-edit me-2"></i>Редактировать профиль
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Основная информация -->
                        <h5 class="mb-3">Основная информация</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Имя *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Телефон</label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                           id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="city" class="form-label">Город</label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror"
                                           id="city" name="city" value="{{ old('city', $user->city) }}">
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Фото профиля -->
                        <h5 class="mb-3 mt-4">Фото профиля</h5>
                        <div class="mb-3">
                            <label for="photo" class="form-label">Загрузить фото</label>
                            <input type="file" class="form-control @error('photo') is-invalid @enderror"
                                   id="photo" name="photo" accept="image/*">
                            @error('photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Поддерживаемые форматы: JPG, PNG, GIF. Максимальный размер: 2MB</div>

                            @if($user->photo)
                                <div class="mt-2">
                                    <p class="text-muted">Текущее фото:</p>
                                    <img src="{{ asset('storage/' . $user->photo) }}"
                                         alt="Текущее фото"
                                         class="rounded"
                                         style="width: 100px; height: 100px; object-fit: cover;">
                                </div>
                            @endif
                        </div>

                        <!-- Личная информация -->
                        <h5 class="mb-3 mt-4">Личная информация</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="religion" class="form-label">Вероисповедание</label>
                                    <select class="form-select @error('religion') is-invalid @enderror"
                                            id="religion" name="religion">
                                        <option value="">Выберите вероисповедание</option>
                                        <option value="Православие" {{ old('religion', $user->religion) == 'Православие' ? 'selected' : '' }}>Православие</option>
                                        <option value="Католицизм" {{ old('religion', $user->religion) == 'Католицизм' ? 'selected' : '' }}>Католицизм</option>
                                        <option value="Протестантизм" {{ old('religion', $user->religion) == 'Протестантизм' ? 'selected' : '' }}>Протестантизм</option>
                                        <option value="Ислам" {{ old('religion', $user->religion) == 'Ислам' ? 'selected' : '' }}>Ислам</option>
                                        <option value="Иудаизм" {{ old('religion', $user->religion) == 'Иудаизм' ? 'selected' : '' }}>Иудаизм</option>
                                        <option value="Буддизм" {{ old('religion', $user->religion) == 'Буддизм' ? 'selected' : '' }}>Буддизм</option>
                                        <option value="Другое" {{ old('religion', $user->religion) == 'Другое' ? 'selected' : '' }}>Другое</option>
                                        <option value="Не указано" {{ old('religion', $user->religion) == 'Не указано' ? 'selected' : '' }}>Не указано</option>
                                    </select>
                                    @error('religion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="church" class="form-label">Название церкви</label>
                                    <input type="text" class="form-control @error('church') is-invalid @enderror"
                                           id="church" name="church" value="{{ old('church', $user->church) }}">
                                    @error('church')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="marital_status" class="form-label">Семейное положение</label>
                                    <select class="form-select @error('marital_status') is-invalid @enderror"
                                            id="marital_status" name="marital_status">
                                        <option value="">Выберите семейное положение</option>
                                        <option value="single" {{ old('marital_status', $user->marital_status) == 'single' ? 'selected' : '' }}>Холост/Не замужем</option>
                                        <option value="married" {{ old('marital_status', $user->marital_status) == 'married' ? 'selected' : '' }}>Женат/Замужем</option>
                                        <option value="divorced" {{ old('marital_status', $user->marital_status) == 'divorced' ? 'selected' : '' }}>Разведен/Разведена</option>
                                        <option value="widowed" {{ old('marital_status', $user->marital_status) == 'widowed' ? 'selected' : '' }}>Вдовец/Вдова</option>
                                    </select>
                                    @error('marital_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="education" class="form-label">Образование</label>
                                    <select class="form-select @error('education') is-invalid @enderror"
                                            id="education" name="education">
                                        <option value="">Выберите образование</option>
                                        <option value="Среднее" {{ old('education', $user->education) == 'Среднее' ? 'selected' : '' }}>Среднее</option>
                                        <option value="Среднее специальное" {{ old('education', $user->education) == 'Среднее специальное' ? 'selected' : '' }}>Среднее специальное</option>
                                        <option value="Неполное высшее" {{ old('education', $user->education) == 'Неполное высшее' ? 'selected' : '' }}>Неполное высшее</option>
                                        <option value="Высшее" {{ old('education', $user->education) == 'Высшее' ? 'selected' : '' }}>Высшее</option>
                                        <option value="Магистратура" {{ old('education', $user->education) == 'Магистратура' ? 'selected' : '' }}>Магистратура</option>
                                        <option value="Аспирантура" {{ old('education', $user->education) == 'Аспирантура' ? 'selected' : '' }}>Аспирантура</option>
                                        <option value="Докторантура" {{ old('education', $user->education) == 'Докторантура' ? 'selected' : '' }}>Докторантура</option>
                                    </select>
                                    @error('education')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- О себе -->
                        <div class="mb-3">
                            <label for="about_me" class="form-label">О себе</label>
                            <textarea class="form-control @error('about_me') is-invalid @enderror"
                                      id="about_me" name="about_me" rows="4"
                                      placeholder="Расскажите немного о себе...">{{ old('about_me', $user->about_me) }}</textarea>
                            @error('about_me')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Биография -->
                        <div class="mb-3">
                            <label for="bio" class="form-label">Биография</label>
                            <textarea class="form-control @error('bio') is-invalid @enderror"
                                      id="bio" name="bio" rows="4"
                                      placeholder="Подробная биография...">{{ old('bio', $user->bio) }}</textarea>
                            @error('bio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Статус активности -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                       value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Активный пользователь
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.test-profile') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Назад к профилю
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Сохранить изменения
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Текущий профиль -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2"></i>Текущий профиль
                    </h5>
                </div>
                <div class="card-body text-center">
                    @if($user->photo)
                        <img src="{{ asset('storage/' . $user->photo) }}"
                             alt="Фото пользователя"
                             class="rounded-circle mb-3"
                             style="width: 120px; height: 120px; object-fit: cover;">
                    @else
                        <div class="avatar-lg mx-auto mb-3">
                            <div class="avatar-title bg-primary text-white rounded-circle">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                        </div>
                    @endif

                    <h5>{{ $user->name }}</h5>
                    <p class="text-muted">{{ $user->email }}</p>

                    @if($user->city)
                        <p><i class="fas fa-map-marker-alt me-2"></i>{{ $user->city }}</p>
                    @endif

                    @if($user->religion)
                        <p><i class="fas fa-pray me-2"></i>{{ $user->religion }}</p>
                    @endif
                </div>
            </div>

            <!-- Подсказки -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-lightbulb me-2"></i>Подсказки
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-info-circle text-info me-2"></i>
                            Загружайте качественные фото для лучшего отображения
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-info-circle text-info me-2"></i>
                            Указывайте актуальную информацию о себе
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-info-circle text-info me-2"></i>
                            Поле "О себе" поможет другим узнать вас лучше
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-lg {
    width: 120px;
    height: 120px;
}

.avatar-title {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 2.5rem;
}
</style>
@endsection
