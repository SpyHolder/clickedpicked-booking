<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Utils\PhoneNormalizer;

class PhoneNormalizerTest extends TestCase
{
    /**
     * Test normalisasi nomor lokal 08xxx
     */
    public function test_normalize_local_number(): void
    {
        $result = PhoneNormalizer::normalize('081234567890');
        $this->assertEquals('6281234567890', $result);
    }

    /**
     * Test normalisasi nomor dengan +62
     */
    public function test_normalize_number_with_plus62(): void
    {
        $result = PhoneNormalizer::normalize('+6281234567890');
        $this->assertEquals('6281234567890', $result);
    }

    /**
     * Test normalisasi nomor dengan 62 (tanpa +)
     */
    public function test_normalize_number_with_62(): void
    {
        $result = PhoneNormalizer::normalize('6281234567890');
        $this->assertEquals('6281234567890', $result);
    }

    /**
     * Test normalisasi nomor dengan spasi dan dash
     */
    public function test_normalize_number_with_spaces_and_dashes(): void
    {
        $result = PhoneNormalizer::normalize('+62-812-3456-7890');
        $this->assertEquals('6281234567890', $result);
        
        $result2 = PhoneNormalizer::normalize('0812 3456 7890');
        $this->assertEquals('6281234567890', $result2);
    }

    /**
     * Test normalisasi nomor dengan parentheses
     */
    public function test_normalize_number_with_parentheses(): void
    {
        $result = PhoneNormalizer::normalize('+62 (812) 3456-7890');
        $this->assertEquals('6281234567890', $result);
    }

    /**
     * Test validasi nomor valid
     */
    public function test_is_valid_with_valid_number(): void
    {
        $this->assertTrue(PhoneNormalizer::isValid('081234567890'));
        $this->assertTrue(PhoneNormalizer::isValid('+6281234567890'));
        $this->assertTrue(PhoneNormalizer::isValid('6281234567890'));
    }

    /**
     * Test validasi nomor invalid (terlalu pendek)
     */
    public function test_is_valid_with_short_number(): void
    {
        $this->assertFalse(PhoneNormalizer::isValid('081234'));
    }

    /**
     * Test validasi nomor invalid (terlalu panjang)
     */
    public function test_is_valid_with_long_number(): void
    {
        $this->assertFalse(PhoneNormalizer::isValid('08123456789012345'));
    }

    /**
     * Test validasi nomor invalid (bukan dimulai dengan 0 atau 62)
     */
    public function test_is_valid_with_invalid_prefix(): void
    {
        $this->assertFalse(PhoneNormalizer::isValid('1234567890'));
        $this->assertFalse(PhoneNormalizer::isValid('9876543210'));
    }

    /**
     * Test validasi nomor kosong
     */
    public function test_is_valid_with_empty_number(): void
    {
        $this->assertFalse(PhoneNormalizer::isValid(''));
        $this->assertFalse(PhoneNormalizer::isValid(null));
    }

    /**
     * Test format dengan style default (international)
     */
    public function test_format_international(): void
    {
        $result = PhoneNormalizer::format('081234567890');
        $this->assertEquals('+62 812-3456-7890', $result);
    }

    /**
     * Test format dengan style whatsapp
     */
    public function test_format_whatsapp(): void
    {
        $result = PhoneNormalizer::format('081234567890', 'whatsapp');
        $this->assertEquals('6281234567890', $result);
    }

    /**
     * Test format dengan style local
     */
    public function test_format_local(): void
    {
        $result = PhoneNormalizer::format('081234567890', 'local');
        $this->assertEquals('0812-3456-7890', $result);
    }

    /**
     * Test format dengan style compact
     */
    public function test_format_compact(): void
    {
        $result = PhoneNormalizer::format('081234567890', 'compact');
        $this->assertEquals('081234567890', $result);
    }

    /**
     * Test format dengan nomor invalid
     */
    public function test_format_invalid_number(): void
    {
        $result = PhoneNormalizer::format('123');
        $this->assertEquals('123', $result);
    }

    /**
     * Test edge case: nomor dengan karakter non-digit
     */
    public function test_normalize_with_non_digit_characters(): void
    {
        $result = PhoneNormalizer::normalize('+62 (812) ABC-3456-7890');
        $this->assertEquals('6281234567890', $result);
    }

    /**
     * Test edge case: nomor dengan banyak spasi
     */
    public function test_normalize_with_multiple_spaces(): void
    {
        $result = PhoneNormalizer::normalize('  0812  3456  7890  ');
        $this->assertEquals('6281234567890', $result);
    }
}