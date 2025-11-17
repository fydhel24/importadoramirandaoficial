<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="marca" class="form-label">{{ __('Marca') }}</label>
            <input type="text" name="marca" class="form-control @error('marca') is-invalid @enderror" value="{{ old('marca', $marca?->marca) }}" id="marca" placeholder="Marca">
            {!! $errors->first('marca', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>