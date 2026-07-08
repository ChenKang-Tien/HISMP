<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientMidDialysisRecordAll extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'DtTm',
        'TSSRNB',
        'DMBLDN',
        'TSDLBL',
        'DMBLBP',
        'TSDVNM',
        'DCVEPR',
        'DCARPR',
        'DCTMPV',
        'DSBLFL',
        'DCDITP',
        'DCENDC',
        'DCUFRA',
        'DCUFVO',
        'DCUFTM',
        'TSBCAB',
        'TSBCVA',
        'DCDIFL',
        'DCBLVT',
        'DCDMVT',
        'DCCPTS',
        'DCAVBF',
        'DCHEPR',
        'DCTHBO',
        'DCHEPV',
        'DCBBTP',
        'DCBBCC',
        'DCBBST',
        'DCCPU1',
        'DCPRBV',
        'DCTHTM',
        'DSTHTM',
        'DCBLFL',
        'TSCALR',
        'TSBCSB',
        'TSMAIN',
        'ADPKID',
        'DSUFVO',
        'DSUFPT',
        'DSNPTP',
        'DSDLFW',
        'DSNAGO',
        'DSNAST',
        'DSBIAD',
        'DSDITP',
        'created_at',
        'updated_at',
        // 填上所有欄位
    ];
}
