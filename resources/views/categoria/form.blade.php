<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="categoria" class="form-label">{{ __('Categoria') }}</label>
            <input type="text" name="categoria" class="form-control @error('categoria') is-invalid @enderror" value="{{ old('categoria', $categoria?->categoria) }}" id="categoria" placeholder="Categoria">
            {!! $errors->first('categoria', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>