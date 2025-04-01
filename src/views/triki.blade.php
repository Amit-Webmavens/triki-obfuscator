<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Download Obfuscated Dump</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4"><b>Download Obfuscated Dump</b></h1>
        <div class="row">
            <div class="col-md-6">
                <p><b>Note:</b> <i>Unchecked tables will be ignored during the dump.</i></p>
                <p id="status"></p>
                <form id="dumpForm">
                @csrf
                @forelse($tables as $table)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox"  name="tables[]" value="{{ $table->TABLE_NAME ?? $table->table_name }}" id="{{ $table->TABLE_NAME ?? $table->table_name }}" checked>
                        <label class="form-check-label" for="{{ $table->TABLE_NAME ?? $table->table_name }}">
                            {{ $table->TABLE_NAME ?? $table->table_name }}
                        </label>
                    </div>
                @empty
                    <h2>It supports only mySQL and pgSQL databases.</h2>
                @endforelse
                    <div class="mb-3 mt-3">
                        <input type="email" name="email" class="form-control"  placeholder="Enter your email for notification">
                    </div>
                    <button type="submit" class="btn btn-primary">Download</button>
                </form>
            </div>
            <div class="col-md-6">
                <h3 class="text-center"><b>Available Dumps</b></h3>
                @if(session('success'))
                <div style="color: green;">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                <div style="color: red;">{{ session('error') }}</div>
                @endif

                <table class="table table-striped">
                    <tbody>
                        @forelse ($dumpFiles as $file)
                        <tr>
                            <th scope="row">{{ $file }}</th>
                            <td><a href="{{ route('triki.download.stored', ['filename' => $file]) }}" target="_blank">Download</a></td>
                            <td><a href="{{ route('triki.delete.dump', ['filename' => $file]) }}" class="text-danger delete-btn">Delete</a></td>
                        </tr>
                        @empty
                        <tr>No dumps generated yet.</tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $('#dumpForm').submit(function(e) {
            e.preventDefault();
            $('#status').text('⏳ Dump is being processed...');
            $.ajax({
                url: '{{ route("triki.startDumpJob") }}',
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#status').text('✅ Dump started. Check back later or refresh to see download link.');
                },
                error: function(xhr) {
                    $('#status').text('❌ Error: ' + (xhr.responseJSON?.message || 'Unknown error'));
                }
            });
        });

        $('.delete-btn').click(function () {
            return confirm('Are you sure you want to delete this file?');
        });
    </script>
</body>
</html>
