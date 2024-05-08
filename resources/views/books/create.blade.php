@extends('layouts.base')

@section('create_book')
<div class="container">
    <div class="row my-5">
        <div class="col">
            <div class="card border-0 shadow">
                <div class="card-header  text-white">
                    Add Book
                </div>
                <div class="card-body">
                    <form action="{{ route('books.createBook') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input
                                type="text"
                                class="form-control @error('title') is-invalid @enderror"
                                placeholder="Please enter the title of the book."
                                name="title"
                                id="title"
                            />
                            @error('title')
                                <p class="invalid-feedback">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="author" class="form-label">Author</label>
                            <input
                                type="text"
                                class="form-control @error('title') is-invalid @enderror"
                                placeholder="Please enter the author of the book."
                                name="author"
                                id="author"
                            />
                            @error('author')
                                <p class="invalid-feedback">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <button class="btn btn-primary mt-2">Create</button>                     
                    </form>
                </div>
            </div>                
        </div>
    </div>       
</div>
@endsection
