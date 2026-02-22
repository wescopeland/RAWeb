<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('systems', function (Blueprint $table) {
            $table->json('screenshot_resolutions')->nullable()->after('name_short');
            $table->boolean('supports_resolution_scaling')->default(false)->after('screenshot_resolutions');
        });

        /**
         * Seed known system screenshot resolutions.
         *
         * Each entry is a JSON array of {width, height} objects representing
         * the valid emulator output resolutions for screenshots on that system.
         *
         * IMPORTANT: These are what the libretro core (or RA-verified standalone
         * emulator) actually writes to the framebuffer. These are not raw hardware
         * pixel counts or display aspect ratios.
         *
         * The first resolution in each array is the "most common", and it's used
         * as the default for layout shift prevention in the front-end.
         *
         * Unlisted systems stay null, meaning the resolution varies per game and
         * no dimension validation is applied at upload time.
         *
         * Citations point to core source code (retro_get_system_av_info or
         * equivalent geometry structs) where possible.
         */
        $resolutions = [

            // =================================================================
            // HANDHELDS - Fixed LCD resolutions, no PAL/NTSC variance.
            // =================================================================

            // Game Boy - Fixed 160x144 dot-matrix LCD.
            4 => [[160, 144]],

            // Game Boy Color - Same 160x144 LCD as Game Boy.
            6 => [[160, 144]],

            // Game Boy Advance - Fixed 240x160 TFT LCD.
            5 => [[240, 160]],

            // Game Gear - Fixed 160x144 LCD viewport.
            15 => [[160, 144]],

            // Atari Lynx - Fixed 160x102 backlit LCD.
            13 => [[160, 102]],

            // Neo Geo Pocket / Color - Fixed 160x152 TFT LCD.
            14 => [[160, 152]],

            // Pokemon Mini - Fixed 96x64 monochrome LCD.
            // https://docs.libretro.com/library/pokemini/
            24 => [[96, 64]],

            // WonderSwan / Color - 224x144 LCD with physical rotation.
            // Games run horizontal (224x144) or vertical (144x224).
            53 => [[224, 144], [144, 224]],

            // Watara Supervision - Fixed 160x160 monochrome LCD (4 shades).
            // https://en.wikipedia.org/wiki/Watara_Supervision
            63 => [[160, 160]],

            // Mega Duck - Fixed 160x144 LCD (Game Boy clone hardware).
            69 => [[160, 144]],

            // PlayStation Portable - Fixed 480x272 TFT LCD.
            // https://docs.libretro.com/library/ppsspp/
            41 => [[480, 272]],

            // Nintendo DS - Two 256x192 screens stacked into 256x384.
            // https://docs.libretro.com/library/melonds/
            18 => [[256, 384]],

            // Nintendo DSi - Same dual-screen layout as DS, 256x384.
            78 => [[256, 384]],

            // Nintendo 3DS - Top screen is 400x240. Bottom screen is 320x240.
            62 => [[400, 240], [320, 240]],

            // Nokia N-Gage - Fixed 176x208 TFT LCD (portrait).
            // https://en.wikipedia.org/wiki/N-Gage_(device)
            61 => [[176, 208]],

            // =================================================================
            // NINTENDO HOME CONSOLES
            // =================================================================

            // NES/Famicom - PPU outputs 256x240. NTSC cores crop overscan to
            // 256x224 by default. PAL uses the full 256x240.
            7 => [[256, 224], [256, 240]],

            // Famicom Disk System - Same PPU as NES.
            81 => [[256, 224], [256, 240]],

            // SNES/Super Famicom - 256x224 standard. Hi-res Mode 5/6 doubles
            // horizontal to 512. Interlaced doubles vertical. Max 512x478.
            // https://docs.libretro.com/library/snes9x/
            3 => [[256, 224], [256, 239], [512, 224], [512, 239], [512, 448], [512, 478]],

            // Nintendo 64 - 320x240 standard. Expansion Pak games support 640x480 interlaced.
            // https://github.com/libretro/mupen64plus-libretro-nx/blob/develop/libretro/libretro.c#L1441
            2 => [[320, 240], [640, 480]],

            // Virtual Boy - Fixed 384x224 LED display per eye.
            // https://en.wikipedia.org/wiki/Virtual_Boy
            28 => [[384, 224]],

            // GameCube - Dolphin core outputs 640x480 at 1x native.
            // https://docs.libretro.com/library/dolphin/
            16 => [[640, 480]],

            // Wii - Same Dolphin core as GameCube, 640x480 at 1x native.
            // https://docs.libretro.com/library/dolphin/
            19 => [[640, 480]],

            // Wii U - Most games render at 1280x720.
            20 => [[1280, 720]],

            // =================================================================
            // SEGA
            // =================================================================

            // SG-1000 - TMS9918A VDP. 256x192 only, no extended modes.
            // https://docs.libretro.com/library/genesis_plus_gx/
            33 => [[256, 192]],

            // Master System - TMS9918-derived VDP outputs 256x192. SMS2 VDP
            // adds 256x224 and 256x240, used by Codemasters titles and others.
            // https://docs.libretro.com/library/gearsystem/
            // https://www.smspower.org/Development/Modes
            11 => [[256, 192], [256, 224], [256, 240]],

            // Genesis/Mega Drive - H40 (320px, most games) and H32 (256px).
            // NTSC: 224 lines, PAL: 240 lines.
            // https://docs.libretro.com/library/genesis_plus_gx/
            // https://docs.libretro.com/library/picodrive/
            1 => [[320, 224], [256, 224], [320, 240], [256, 240]],

            // Sega CD - Same VDP as Genesis, same resolution modes.
            9 => [[320, 224], [256, 224], [320, 240], [256, 240]],

            // 32X - Inherits Genesis VDP. Most 32X rendering uses 320-wide.
            // PAL outputs 240 lines.
            10 => [[320, 224], [320, 240]],

            // Sega Pico - Same VDP as Genesis.
            68 => [[320, 224], [256, 224], [320, 240], [256, 240]],

            // Saturn - VDP2 supports widths 320/352 (lo-res) and 640/704
            // (hi-res), heights 224/240 (NTSC) and 256 (PAL). Interlaced
            // doubles vertical. Most games use 320x224 or 352x224.
            // https://docs.libretro.com/library/beetle_saturn/
            39 => [
                [320, 224], [352, 224], [320, 240], [352, 240],  // lo-res NTSC/PAL
                [320, 256], [352, 256],                          // lo-res PAL
                [640, 224], [704, 224], [640, 240], [704, 240],  // hi-res NTSC/PAL
                [640, 448], [704, 448], [640, 480], [704, 480],  // interlaced
            ],

            // Dreamcast - Flycast always outputs 640x480. Games rendering at
            // 320x240 are pixel-doubled by hardware.
            // https://github.com/libretro/flycast/blob/b897744e27c730c7519784b2aef12ba7f658de31/core/libretro/libretro.cpp#L318
            40 => [[640, 480]],

            // =================================================================
            // SONY
            // =================================================================

            // PlayStation - GPU supports widths 256/320/368/512/640 at 240
            // lines (progressive) or 480 (interlaced). No 224-line mode exists
            // on PS1 unlike NES/SNES. Most games use 320x240.
            // https://docs.libretro.com/library/beetle_psx/
            // https://psx-spx.consoledev.net/graphicsprocessingunitgpu/
            12 => [
                [320, 240], [256, 240], [368, 240], [512, 240], [640, 240],  // progressive
                [320, 480], [256, 480], [368, 480], [512, 480], [640, 480],  // interlaced
            ],

            // PlayStation 2 - PS2 always outputs 640x448 (NTSC) or 640x512
            // (PAL). Games rendering at 512-wide get stretched to fill 640.
            // 512-wide variants appear when screen offsets are disabled.
            // https://github.com/PCSX2/pcsx2/issues/10922#issuecomment-1997653184
            21 => [[640, 448], [512, 448], [640, 512], [512, 512]],

            // =================================================================
            // MICROSOFT
            // =================================================================

            // Xbox - 640x480 baseline output.
            // https://www.copetti.org/writings/consoles/xbox/
            22 => [[640, 480]],

            // =================================================================
            // ATARI
            // =================================================================

            // Atari 5200 - a5200 core outputs 336x240 (includes overscan
            // borders around the ANTIC chip's 320x192 active area).
            // https://docs.libretro.com/library/atari800/
            50 => [[336, 240]],

            // Atari 7800 - ProSystem core: 320x223 (NTSC), 320x272 (PAL).
            // https://docs.libretro.com/library/prosystem/
            51 => [[320, 223], [320, 272]],

            // Atari Jaguar - Most games use 320x240.
            // https://github.com/libretro/virtualjaguar-libretro/blob/48096c1f6f8b98cfff048a5cb4e6a86686631072/libretro.c#L860
            17 => [[320, 240]],

            // Atari Jaguar CD - Same hardware as Jaguar.
            // https://github.com/libretro/virtualjaguar-libretro/blob/48096c1f6f8b98cfff048a5cb4e6a86686631072/libretro.c#L860
            77 => [[320, 240]],

            // =================================================================
            // NEC
            // =================================================================

            // PC Engine/TurboGrafx-16 - HuC6270 VDC supports widths 256, 336,
            // and 512 with ~239 visible lines. Beetle PCE FAST outputs 512x243
            // to handle mid-frame width switching.
            // https://docs.libretro.com/library/beetle_pce_fast/
            8 => [[256, 239], [336, 239], [512, 243]],

            // PC Engine CD - Same hardware as PC Engine.
            // https://docs.libretro.com/library/beetle_pce_fast/
            76 => [[256, 239], [336, 239], [512, 243]],

            // PC-FX - Most games use 256x240 or 341x240.
            // https://github.com/libretro/beetle-pcfx-libretro/blob/dd04cef9355286488a1d78ff18c4c848a1575540/libretro.cpp#L441
            49 => [[256, 240], [341, 240]],

            // =================================================================
            // SNK
            // =================================================================

            // Neo Geo CD - LSPC2 outputs 320x224. Many games use the center
            // 304 pixels only (16px black borders).
            // https://www.chibiakumas.com/68000/neogeo.php
            56 => [[320, 224], [304, 224]],

            // =================================================================
            // OTHER CONSOLES
            // =================================================================

            // 3DO - Opera core: 320x240 default, 640x480 with High Resolution
            // option enabled.
            // https://docs.libretro.com/library/opera/
            43 => [[320, 240], [640, 480]],

            // Philips CD-i
            // http://www.icdia.co.uk/docs_sw/vcd_on_cdi_311.pdf
            42 => [[384, 240], [384, 280]],

            // ColecoVision - TMS9928A VDP, 256x192 only.
            // https://www.msx.org/forum/msx-talk/development/setting-graphics-mode-7-and-sprites-also-bluemsx-vram-debugging
            44 => [[256, 192]],

            // Intellivision - FreeIntv core outputs 352x224 by default.
            // https://github.com/libretro/FreeIntv/blob/df5a5312985b66b1ec71b496868641e40b7ad1c9/src/libretro.c#L53
            45 => [[352, 224]],

            // Magnavox Odyssey 2
            // https://docs.libretro.com/library/o2em/
            23 => [[340, 250]],

            // Fairchild Channel F - FreeChaF core outputs 306x192.
            // https://github.com/libretro/FreeChaF/blob/cdb8ad6fcecb276761b193650f5ce9ae8b878067/src/libretro.c#L40
            57 => [[306, 192]],

            // Arcadia 2001
            // https://docs.retroachievements.org/guidelines/content/badge-and-icon-guidelines.html
            73 => [[146, 240]],

            // Interton VC 4000
            // https://docs.retroachievements.org/guidelines/content/badge-and-icon-guidelines.html
            74 => [[146, 240]],

            // Elektor TV Games Computer
            // https://docs.retroachievements.org/guidelines/content/badge-and-icon-guidelines.html
            75 => [[146, 240]],

            // Cassette Vision - Hardware spec.
            // https://en.wikipedia.org/wiki/Cassette_Vision
            54 => [[128, 192]],

            // Super Cassette Vision - Hardware spec.
            // https://en.wikipedia.org/wiki/Super_Cassette_Vision
            55 => [[256, 192]],

            // Vectrex - Vector display with no fixed pixel resolution. The vecx
            // core rasterizes to 330x410 (portrait matches original CRT).
            // https://docs.libretro.com/library/vecx/
            46 => [[330, 410]],

            // Zeebo - ARM-based console with 800x480 display.
            // https://en.wikipedia.org/wiki/Zeebo
            70 => [[800, 480]],

            // =================================================================
            // FANTASY CONSOLES & CALCULATORS
            // =================================================================

            // Arduboy - Fixed 128x64 1-bit OLED.
            // https://docs.libretro.com/library/ardens/
            71 => [[128, 64]],

            // WASM-4 - Spec defines 160x160 at 4 colors.
            // https://github.com/aduros/wasm4/blob/main/runtimes/native/src/backend/main_libretro.c#L266
            72 => [[160, 160]],

            // TIC-80 - Spec defines 240x136.
            // https://github.com/nesbox/TIC-80/blob/main/src/system/libretro/tic80_libretro.c#L433
            65 => [[240, 136]],

            // TI-83 - 96x64 monochrome LCD.
            79 => [[96, 64]],

            // =================================================================
            // COMPUTERS - Fixed or well-defined core output.
            // =================================================================

            // MSX
            29 => [[272, 240]],

            // VIC-20 - VICE xvic core: 448x284 (PAL), 400x234 (NTSC).
            // https://docs.libretro.com/library/vice/
            // https://github.com/libretro/vice-libretro/blob/master/libretro/libretro-core.h
            34 => [[448, 284], [400, 234]],

            // Atari ST - Hatari core scales all ST display modes into its
            // "Internal Resolution" buffer. Default is 640x480.
            // https://github.com/libretro/hatari/blob/7008194d3f951a157997f67a820578f56f7feee0/libretro/libretro.c#L976
            36 => [[640, 480], [320, 200]],

            // Amstrad CPC
            37 => [[320, 226]],

            // Apple II - RAppleWin outputs a fixed 560x384 framebuffer. All
            // video modes (HGR, LoRes, DHGR) render into this buffer.
            // https://github.com/AppleWin/AppleWin
            38 => [[560, 384], [320, 219]],

            // PC-8000/8800 - QUASI88 core always outputs 640x400 regardless
            // of PC-88 video mode.
            // https://docs.libretro.com/library/quasi88/
            47 => [[640, 400]],

            // PC-9800 - NP2kai core: 640x400 (nearly all PC-98 games).
            // https://forums.libretro.com/t/neko-project-ii-kai-pc-9801-core-different-nekop2-meowpc98/11086/41
            48 => [[640, 400]],

            // Sharp X68000 - PX68k core outputs a fixed 800x600 framebuffer.
            // All native modes are internally scaled by the core.
            // https://docs.libretro.com/library/px68k/
            // https://github.com/libretro/px68k-libretro/blob/9dfa6abc25ddd6e597790f7a535cd0a1d7f9c385/libretro.c#L129
            52 => [[800, 600]],

            // ZX Spectrum - Fuse core: 320x240 (standard models). Timex
            // models (TC2048, TC2068) output 640x480.
            // https://github.com/libretro/fuse-libretro/blob/cad85b7b1b864c65734f71aa4a510b6f6536881c/src/libretro.c#L499
            59 => [[320, 240], [640, 480]],

            // Sharp X1
            // https://github.com/libretro/xmil-libretro/blob/master/libretro/xmil.h
            64 => [[320, 200], [640, 400]],

            // Thomson TO8
            // https://docs.libretro.com/library/theodore/
            66 => [[672, 432]],

            // =================================================================
            // GAME-DEPENDENT - Left null. No dimension validation at upload.
            // =================================================================

            // Atari 2600 (ID 25) - TIA is 160px wide but vertical scanline
            // count varies per game (~160 to ~230+). Stella core is dynamic.
            // https://github.com/libretro/stella2014-libretro/blob/3cc89f0d316d6c924a5e3f4011d17421df58e615/libretro.cxx#L1020

            // Arcade (ID 27) - Every board has different hardware.
            // FBNeo outputs per-game native resolution.

            // DOS (ID 26) - DOSBox Pure dynamically resizes per video mode.

            // Amiga (ID 35) - PUAE core varies by game mode and PAL/NTSC.

            // Oric (ID 32)

            // ZX81 (ID 31)

            // Commodore 64 (ID 30)

            // Uzebox (ID 80) - Resolution varies per game mode.

            // Game & Watch (ID 60) - gw-libretro uses per-game dimensions
            // (each .mgw file defines its own resolution). No fixed output.

            // FM Towns (ID 58)

            // PC-6000 (ID 67)

            // =================================================================
            // NON-GAME - Not applicable.
            // =================================================================

            // Hubs (ID 100), Events (ID 101), Standalone (ID 102)
        ];

        foreach ($resolutions as $systemId => $modes) {
            $json = json_encode(array_map(
                fn (array $pair) => ['width' => $pair[0], 'height' => $pair[1]],
                $modes,
            ));

            DB::table('systems')->where('id', $systemId)->update([
                'screenshot_resolutions' => $json,
            ]);
        }

        // Systems where the emulator supports internal resolution scaling
        // (2x, 3x, etc). Validation accepts screenshots that are exact
        // integer multiples of any base resolution, capped at 3x.
        $scalingSystems = [
            41,  // PSP
            12,  // PlayStation
            21,  // PlayStation 2
            2,   // Nintendo 64
            16,  // GameCube
            19,  // Wii
            40,  // Dreamcast
            18,  // Nintendo DS
            78,  // Nintendo DSi
            62,  // Nintendo 3DS
        ];

        DB::table('systems')->whereIn('id', $scalingSystems)->update([
            'supports_resolution_scaling' => true,
        ]);
    }

    public function down(): void
    {
        Schema::table('systems', function (Blueprint $table) {
            $table->dropColumn(['screenshot_resolutions', 'supports_resolution_scaling']);
        });
    }
};
