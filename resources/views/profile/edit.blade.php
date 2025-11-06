@extends('layouts.app')

@section('title', 'Редактировать профиль')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="mb-0"><i class="fas fa-user-edit me-2 text-primary"></i>Редактировать профиль</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <!-- Photo Upload -->
                        <div class="row mb-4">
                            <div class="col-12 text-center">
                                <div class="avatar-preview mb-3">
                                    @if($user->photo)
                                        <img id="avatarPreview" src="{{ Storage::url($user->photo) }}" alt="Avatar" class="rounded-circle mb-2" style="width: 150px; height: 150px; object-fit: cover;">
                                    @else
                                        <div id="avatarPreview" class="bg-primary text-white rounded-circle mx-auto d-flex align-items-center justify-content-center mb-2" style="width: 150px; height: 150px; font-size: 4rem;">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <input type="file" name="photo" id="photo" class="form-control d-none" accept="image/*" onchange="previewImage(this)">
                                <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('photo').click()">
                                    <i class="fas fa-camera me-2"></i>Изменить фото
                                </button>
                            </div>
                        </div>

                        <!-- Basic Information -->
                        <h5 class="mb-3"><i class="fas fa-info-circle me-2 text-primary"></i>Основная информация</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Имя *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Телефон</label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="city" class="form-label">Город</label>
                                <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                       id="city" name="city" value="{{ old('city', $user->city) }}">
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Personal Information -->
                        <h5 class="mb-3 mt-4"><i class="fas fa-user-circle me-2 text-primary"></i>Личная информация</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="religion" class="form-label">Вероисповедание</label>
                                <input type="text" class="form-control @error('religion') is-invalid @enderror" 
                                       id="religion" name="religion" value="{{ old('religion', $user->religion) }}">
                                @error('religion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="marital_status" class="form-label">Семейное положение</label>
                                <select class="form-select @error('marital_status') is-invalid @enderror" 
                                        id="marital_status" name="marital_status">
                                    <option value="">Выберите</option>
                                    <option value="single" {{ old('marital_status', $user->marital_status) === 'single' ? 'selected' : '' }}>Холост/Не замужем</option>
                                    <option value="married" {{ old('marital_status', $user->marital_status) === 'married' ? 'selected' : '' }}>Женат/Замужем</option>
                                    <option value="divorced" {{ old('marital_status', $user->marital_status) === 'divorced' ? 'selected' : '' }}>Разведен(а)</option>
                                    <option value="widowed" {{ old('marital_status', $user->marital_status) === 'widowed' ? 'selected' : '' }}>Вдовец/Вдова</option>
                                </select>
                                @error('marital_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="church" class="form-label">Церковь</label>
                                <input type="text" class="form-control @error('church') is-invalid @enderror" 
                                       id="church" name="church" value="{{ old('church', $user->church) }}">
                                @error('church')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="education" class="form-label">Образование</label>
                                <input type="text" class="form-control @error('education') is-invalid @enderror" 
                                       id="education" name="education" value="{{ old('education', $user->education) }}">
                                @error('education')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- About -->
                        <div class="mb-3">
                            <label for="about_me" class="form-label">О себе</label>
                            <textarea class="form-control @error('about_me') is-invalid @enderror" 
                                      id="about_me" name="about_me" rows="5">{{ old('about_me', $user->about_me) }}</textarea>
                            @error('about_me')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Расскажите о себе, своих интересах и целях обучения</small>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('profile.show') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Отмена
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Сохранить изменения
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatarPreview');
            if (preview.tagName === 'IMG') {
                preview.src = e.target.result;
            } else {
                preview.outerHTML = `<img id="avatarPreview" src="${e.target.result}" alt="Avatar" class="rounded-circle mb-2" style="width: 150px; height: 150px; object-fit: cover;">`;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<style>
.avatar-preview img {
    border: 4px solid #fff;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
</style>
@endsection

