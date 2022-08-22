<?php

declare(strict_types=1);

namespace EinarHansen\Http\Enums;

enum StreamMode: string
{
    case Read = 'r';
    case ReadWrite = 'r+';
    case WriteOverwrite = 'w';
    case WriteReadOverwrite = 'w+';
    case Append = 'a';
    case AppendRead = 'a+';

    public function description(): string
    {
        return match ($this) {
            static::Read => 'Open for reading only; place the file pointer at the beginning of the file.',
            static::ReadWrite => 'Open for reading and writing; place the file pointer at the beginning of the file.',
            static::WriteOverwrite => 'Open for writing only; place the file pointer at the beginning of the file and truncate the file to zero length. If the file does not exist, attempt to create it',
            static::WriteReadOverwrite => "Open for reading and writing; otherwise it has the same behavior as 'w'.",
            static::Append => 'Open for writing only; place the file pointer at the end of the file. If the file does not exist, attempt to create it. In this mode, fseek() has no effect, writes are always appended.',
            static::AppendRead => 'Open for reading and writing; place the file pointer at the end of the file. If the file does not exist, attempt to create it. In this mode, fseek() only affects the reading position, writes are always appended.',
            default => 'No description available.',
        };
    }
}
