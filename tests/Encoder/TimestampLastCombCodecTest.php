<?php

declare(strict_types=1);

namespace Ramsey\Uuid\Test\Encoder;

use Mockery;
use PHPUnit\Framework\MockObject\MockObject;
use Ramsey\Uuid\Builder\UuidBuilderInterface;
use Ramsey\Uuid\Codec\CodecInterface;
use Ramsey\Uuid\Codec\TimestampLastCombCodec;
use Ramsey\Uuid\Rfc4122\Fields;
use Ramsey\Uuid\Rfc4122\FieldsInterface;
use Ramsey\Uuid\Test\TestCase;
use Ramsey\Uuid\UuidInterface;

use function hex2bin;
use function implode;

class TimestampLastCombCodecTest extends TestCase
{
    /**
     * @var CodecInterface
     */
    private $codec;

    /**
     * @var MockObject & UuidBuilderInterface
     */
    private $builderMock;

    protected function setUp(): void
    {
        $this->builderMock = $this->getMockBuilder(UuidBuilderInterface::class)->getMock();
        $this->codec = new TimestampLastCombCodec($this->builderMock);
    }

    public function testEncoding(): void
    {
        /** @var non-empty-string $bytes */
        $bytes = (string) hex2bin('0800200c9a6611e19b21ff6f8cb0c57d');
        $fields = new Fields($bytes);

        $uuidMock = Mockery::mock(UuidInterface::class, [
            'getFields' => $fields,
        ]);

        $encodedUuid = $this->codec->encode($uuidMock);

        $this->assertSame('0800200c-9a66-11e1-9b21-ff6f8cb0c57d', $encodedUuid);
    }

    public function testBinaryEncoding(): void
    {
        $fields = Mockery::mock(FieldsInterface::class, [
            'getBytes' => hex2bin('0800200c9a6611e19b21ff6f8cb0c57d'),
        ]);

        /** @var MockObject & UuidInterface $uuidMock */
        $uuidMock = $this->getMockBuilder(UuidInterface::class)->getMock();
        $uuidMock->expects($this->any())->method('getFields')->willReturn($fields);

        $encodedUuid = $this->codec->encodeBinary($uuidMock);

        $this->assertSame(hex2bin('0800200c9a6611e19b21ff6f8cb0c57d'), $encodedUuid);
    }

    public function testDecoding(): void
    {
        $this->builderMock->expects($this->exactly(1))
            ->method('build')
            ->with(
                $this->codec,
                hex2bin(implode('', [
                    'time_low' => '0800200c',
                    'time_mid' => '9a66',
                    'time_hi_and_version' => '11e1',
                    'clock_seq_hi_and_reserved' => '9b',
                    'clock_seq_low' => '21',
                    'node' => 'ff6f8cb0c57d',
                ]))
            );
        $this->codec->decode('0800200c-9a66-11e1-9b21-ff6f8cb0c57d');
    }

    public function testBinaryDecoding(): void
    {
        $this->builderMock->expects($this->exactly(1))
            ->method('build')
            ->with(
                $this->codec,
                hex2bin(implode('', [
                    'time_low' => '0800200c',
                    'time_mid' => '9a66',
                    'time_hi_and_version' => '11e1',
                    'clock_seq_hi_and_reserved' => '9b',
                    'clock_seq_low' => '21',
                    'node' => 'ff6f8cb0c57d',
                ]))
            );

        /** @var non-empty-string $bytes */
        $bytes = (string) hex2bin('0800200c9a6611e19b21ff6f8cb0c57d');
        $this->codec->decodeBytes($bytes);
    }
}
