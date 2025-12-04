@extends('layouts.app')

@section('title', 'Сертификат')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-certificate me-2"></i>Сертификат
                    </h4>
                </div>
                <div class="card-body text-center p-4">
                    <div class="mb-4">
                        <p class="text-muted mb-2">Номер сертификата: <strong>{{ $certificate->certificate_number }}</strong></p>
                        <p class="text-muted mb-2">Дата выдачи: <strong>{{ $certificate->issued_at->format('d.m.Y') }}</strong></p>
                    </div>
                    
                    <div class="certificate-image-container mb-4">
                        <img src="{{ Storage::url($certificate->image_path) }}" 
                             alt="Сертификат" 
                             class="img-fluid border rounded shadow-sm"
                             style="max-width: 100%; height: auto;">
                    </div>
                    
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <a href="{{ route('certificates.download', $certificate) }}" class="btn btn-primary">
                            <i class="fas fa-download me-2"></i>Скачать сертификат
                        </a>
                        <button onclick="window.print()" class="btn btn-outline-primary">
                            <i class="fas fa-print me-2"></i>Печать
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .card-header, .btn, .container {
        display: none;
    }
    .certificate-image-container {
        page-break-inside: avoid;
    }
}
</style>
@endsection
