@extends('layouts.base')

@section('book_list')
<div class="container">
    <div class="row my-5">
        <div class="col">
            <div class="card border-0 shadow">
                <div class="card-header  text-white">
                    Books
                </div>

                <div>
                    <form action="" method="GET">
                        <div class="row m-2">
                            <div class="col-9 pt-4 ps-3">
                                <!-- 
                                    Setting "{{ Request::get('keyword') }}" as value for input will retain the search keyword
                                    upon clicking the Search button
                                -->
                                <input type="text" class="form-control" value="{{ Request::get('keyword') }}" name="keyword" placeholder="Search keyword for title or author" />
                            </div>
                            <div class="col-3 pt-4">
                                <button type="submit" class="btn btn-primary" style="width: 97%">Search</button>
                            </div>
                        <div />
                    </form>
                </div>

                <div class="card-body pb-0">

                    <!-- A success message will be displayed upon successfully creating a book record -->
                    @if(session()->has('success'))
                        <div class="alert alert-success">
                            {{ session()->get('success') }}
                        </div>
                    @endif

                    @if ($books->isNotEmpty())
                        <table class="table table-striped mt-3">
                            <thead class="table-dark">
                                <tr>
                                    <th>
                                        <div class="d-flex justify-content-between">
                                            <span>Title</span>
                                            <a href="#" onclick="renderTitleSortedListView()"><i class="fa-solid fa-arrow-down-a-z"></i></a>
                                        </div>
                                    </th>
                                    <th>
                                    <div class="d-flex justify-content-between">
                                        <span>Author</span>
                                        <a href="#" onclick="renderAuthorSortedListView()"><i class="fa-solid fa-arrow-down-a-z"></i></a>
                                    </div>
                                    </th>
                                    <th width="150">Action</th>
                                </tr>
                                <tbody>

                                    @if ($books->isNotEmpty())
                                        <!--
                                            This will loop through all the book records
                                            that return from 'app/Http/Controllers/BookController::renderListView'
                                        -->
                                        @foreach ($books as $book)
                                        <tr>
                                            <td>{{ $book->title }}</td>
                                            <td>{{ $book->author }}</td>
                                            <td>
                                                <a href="{{ route('books.renderUpdateView', $book->id) }}" class="btn btn-primary btn-sm">
                                                    <i class="fa-regular fa-pen-to-square"></i>
                                                </a>
                                                <a href="#" onclick="deleteBook({{ $book->id }})" class="btn btn-danger btn-sm">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @endif

                                </tbody>
                            </thead>
                        </table>
                    @else
                        <h1 class="container">
                            Either no book records are found in the database or no matching search results.
                        </h1>
                    @endif

                    <!-- Download ZIP files containing both CSV and XML files -->
                    @if ($books->isNotEmpty())
                        <form action="{{ route('download.zip') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="d-flex align-items-center mt-1 mb-3 @error('generateTitle') is-invalid @enderror">
                                <button type="submit" class="btn btn-primary mt-2">Export CSV & XML</button>
                                
                                <div class="ms-4">
                                    <input
                                        type="checkbox"
                                        id="generateTitle"
                                        name="generateTitle"
                                        class="@error('generateTitle') is-invalid @enderror"
                                    />
                                    <label for="generateTitle">
                                        Title
                                    </label>
                                </div>

                                <div class="ms-4">
                                    <input
                                        type="checkbox"
                                        id="generateAuthor"
                                        name="generateAuthor"
                                        class="@error('generateAuthor') is-invalid @enderror"
                                    />
                                    <label for="generateAuthor">
                                        Author
                                    </label>
                                </div>
                            </div>

                            @error('generateTitle')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </form>
                    @endif

                    <!--  
                        If there are records existing in 'books' table inside DB and the count is more than the value
                        set to paginate, '$books->links()' will display a pagination. 

                        Currently, paginate is set to 5 in 'app/Http/Controllers/BookController::renderListView'

                        'app/Providers/AppServiceProvider.php::register' function must contain 'Paginator::useBootstrapFive();'
                        for bootstrap to work for pagination.
                    -->
                    @if ($books->isNotEmpty())
                        {{ $books->links() }}                
                    @endif

                </div>
                
            </div>                
        </div>
    </div>       
</div>
@endsection

@section('script')
<!-- 
    'deleteBook' function is called upon clicking the delete button of a corresponding book record.
    Will be redirected to book list view after successfully deleting a book record.
-->
<script>
    function deleteBook(id) {
        if(confirm("Are you sure you want to delete")) {
            $.ajax({
                url: '{{ route("books.deleteBook") }}',
                type: 'delete',
                data: {id: id},
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    window.location.href = "{{ route('books.renderListView') }}"
                }
            })
        }
    }

    function renderTitleSortedListView() {
        $.ajax({
            url: "{{ route('books.renderTitleSortedListView') }}",
            success: function(response) {
                window.location.href = "{{ route('books.renderTitleSortedListView') }}"
            }
        })
    }

    function renderAuthorSortedListView() {
        $.ajax({
            url: "{{ route('books.renderAuthorSortedListView') }}",
            success: function(response) {
                window.location.href = "{{ route('books.renderAuthorSortedListView') }}"
            }
        })
    }
</script>
@endsection