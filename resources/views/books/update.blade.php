@extends('layouts.base')

@section('update_book')
<div class="container">
    <div class="row my-5">
        <div class="col">
            <div class="card border-0 shadow">
                <div class="card-header  text-white">
                    Update Book
                </div>
                <div class="card-body">
                    <form action="{{ route('books.updateBook', $book->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input
                                type="text"
                                class="form-control @error('title') is-invalid @enderror"
                                placeholder="Please enter the title of the book."
                                name="title"
                                id="title"
                                value="{{ old('title', $book->title) }}"
                            />
                            @error('title')
                                <p class="invalid-feedback">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="author" class="form-label">Author</label>
                            <input
                                type="text"
                                class="form-control @error('author') is-invalid @enderror"
                                placeholder="Please enter the author of the book."
                                name="author"
                                id="author"
                                value="{{ old('author', $book->author) }}"
                            />
                            @error('author')
                                <p class="invalid-feedback">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <button class="btn btn-primary mt-2">Update</button>                     
                    </form>
                </div>
            </div>                
        </div>
    </div>       
</div>
@endsection
