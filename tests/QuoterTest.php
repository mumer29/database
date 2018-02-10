<?php declare(strict_types=1);

namespace Odan\Test;

use Odan\Database\Quoter;
use Odan\Database\RawExp;
use PDO;

/**
 * @coversDefaultClass Odan\Database\Quoter
 */
class QuoterTest extends BaseTest
{
    /**
     * Test create object.
     *
     * @return void
     */
    public function testInstance()
    {
        $connection = $this->getConnection()->getQuoter();
        $this->assertInstanceOf(Quoter::class, $connection);
    }

    /**
     * @param PDO $pdo
     * @return PDO
     */
    protected function pdoMethod(PDO $pdo)
    {
        return $pdo;
    }

    /**
     * Test
     *
     * @return void
     * @covers ::quoteValue
     */
    public function testEsc()
    {
        $quoter = $this->getConnection()->getQuoter();
        $this->assertSame('NULL', $quoter->quoteValue(null));
        $this->assertSame("'\\0'", $quoter->quoteValue("\0"));
        $this->assertSame("'0'", $quoter->quoteValue(0));
        $this->assertSame("'0'", $quoter->quoteValue('0'));
        $this->assertSame("''", $quoter->quoteValue(false));
        $this->assertSame("'1'", $quoter->quoteValue(true));
        $this->assertSame("'-1'", $quoter->quoteValue(-1));
        $this->assertSame("'abc123'", $quoter->quoteValue("abc123"));
        $this->assertSame("'öäüÖÄÜß'", $quoter->quoteValue("öäüÖÄÜß"));
        $this->assertSame("'?'", $quoter->quoteValue('?'));
        $this->assertSame("':'", $quoter->quoteValue(':'));
        $this->assertSame("'\\''", $quoter->quoteValue("'"));
        $this->assertSame("'\\\"'", $quoter->quoteValue("\""));
        $this->assertSame("'\\\\'", $quoter->quoteValue("\\"));
        $this->assertSame("'\\0'", $quoter->quoteValue("\x00"));
        $this->assertSame("'\\Z'", $quoter->quoteValue("\x1a"));
        $this->assertSame("'\\n'", $quoter->quoteValue("\n"));
        $this->assertSame("'\\r'", $quoter->quoteValue("\r"));
        $this->assertSame("','", $quoter->quoteValue(","));
        $this->assertSame("'\\','", $quoter->quoteValue("',"));
        $this->assertSame("'`'", $quoter->quoteValue("`"));
        $this->assertSame("'%s'", $quoter->quoteValue("%s"));
        $this->assertSame("'Naughty \\' string'", $quoter->quoteValue("Naughty ' string"));
        $this->assertSame("'@þÿ€'", $quoter->quoteValue("@þÿ€"));
        // Injection patterns
        $this->assertSame("'\\' OR \\'\\'=\\''", $quoter->quoteValue("' OR ''='"));
        $this->assertSame("'1\\' or \\'1\\' = \\'1'", $quoter->quoteValue("1' or '1' = '1"));
        $this->assertSame("'1\\' or \\'1\\' = \\'1\\'))/*'", $quoter->quoteValue("1' or '1' = '1'))/*"));
    }

    /**
     * Test
     *
     * @return void
     * @covers ::quoteName
     * @covers ::quoteNameWithSeparator
     * @covers ::quoteIdentifier
     */
    public function testIdent()
    {
        $quoter = $this->getConnection()->getQuoter();

        $this->assertSame("``", $quoter->quoteName(''));
        $this->assertSame("*", $quoter->quoteName('*'));

        // Table
        $this->assertSame("`abc123`", $quoter->quoteName("abc123"));
        $this->assertSame("`user_roles`", $quoter->quoteName("user_roles "));
        $this->assertSame("`öäüÖÄÜß`", $quoter->quoteName("öäüÖÄÜß"));
        $this->assertSame("`table`.*", $quoter->quoteName("table.*"));

        // Table with alias
        $this->assertSame("`users` `u`", $quoter->quoteName("users u"));
        $this->assertSame("`users` AS `u`", $quoter->quoteName("users AS u"));

        // With database name
        $this->assertSame("`dbname`.`tablename`", $quoter->quoteName("dbname.tablename"));
        $this->assertSame("`dbname`.`tablename`.`field`", $quoter->quoteName("dbname.tablename.field"));
        // Alias.field AS thing
        $this->assertSame("`dbname`.`tablename`.`field` AS `thing`", $quoter->quoteName("dbname.tablename.field AS thing"));

        $this->assertSame("`.`", $quoter->quoteName('.'));
        $this->assertSame("`?`", $quoter->quoteName('?'));
        $this->assertSame("`:`", $quoter->quoteName(':'));
        $this->assertSame("`,`", $quoter->quoteName(","));
        $this->assertSame("`',`", $quoter->quoteName("',"));
        $this->assertSame("````", $quoter->quoteName("`"));
        $this->assertSame("`%s`", $quoter->quoteName("%s"));
        $this->assertSame("`Naughty-'-string`", $quoter->quoteName("Naughty-'-string"));
        $this->assertSame("`@þÿ€`", $quoter->quoteName("@þÿ€"));
    }

    /**
     * Test
     *
     * @return void
     * @covers ::quoteArray
     */
    public function testQuoteArray()
    {
        $quoter = $this->getConnection()->getQuoter();
        $this->assertSame([], $quoter->quoteArray([]));

        $row = ['1', '2', '3', null];
        $this->assertSame(["'1'", "'2'", "'3'", 'NULL'], $quoter->quoteArray($row));
    }


    /**
     * Test
     *
     * @return void
     * @covers ::quoteNames
     */
    public function testQuoteNames()
    {
        $quoter = $this->getConnection()->getQuoter();
        $this->assertSame([], $quoter->quoteNames([]));

        $row = ['a', 'a.b', 'a.b.c', new RawExp('a.z')];
        $this->assertSame(["`a`", "`a`.`b`", "`a`.`b`.`c`", "a.z"], $quoter->quoteNames($row));
    }
}
